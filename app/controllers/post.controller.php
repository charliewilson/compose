<?php
namespace nano;

use PDO;
use PDOException;

class PostController {
  
  private $app;
  
  function __construct(App $app) {
    $this->app = $app;
  }
  
  public function getAll($options = []) {
    
    $defaultOptions = [
      "includeDrafts" => false
    ];
    foreach($options as $option=>$value) {
      if (isset($defaultOptions[$option])) {
        $defaultOptions[$option] = $value;
      }
    }
    
    if ($defaultOptions["includeDrafts"] == true) {
      $statement = "SELECT * FROM `posts` ORDER BY `timestamp` DESC";
    } else {
      $statement = "SELECT * FROM `posts` WHERE published = 1 ORDER BY `timestamp` DESC";
    }
    
    $people = $this->app->db->prepare($statement);
    $people->execute();
  
    return $people->fetchAll(PDO::FETCH_CLASS,'\nano\Post', [
      $this->app->db
    ]);
    
  }
  
  public function get($id, $options = []) {
    
    $defaultOptions = [
    
    ];
    foreach($options as $option=>$value) {
      if (isset($defaultOptions[$option])) {
        $defaultOptions[$option] = $value;
      }
    }
    
    $people = $this->app->db->prepare("SELECT * FROM `posts` WHERE `id` = :id");
    $people->execute([
      ":id" => filter_var($id, FILTER_SANITIZE_NUMBER_INT)
    ]);
    
    return $people->fetchAll(PDO::FETCH_CLASS,'\nano\Post', [
      $this->app->db
    ]);
    
  }
  
  public function create($fields) {
    
    $timestamp = filter_var($fields['timestamp'], FILTER_SANITIZE_STRING);
    $type = filter_var($fields['type'], FILTER_SANITIZE_STRING);
    $title = htmlentities($fields['title']);
    $body = htmlentities($fields['body']);
    $published = (array_key_exists("published", $fields)) ? 1 : 0;
    
    try {
      $people = $this->app->db->prepare("
    INSERT INTO `posts`(`id`,`timestamp`,`type`,`title`,`body`,`published`)
    VALUES (:id, :timestamp, :type, :title, :body, :published)
    ");
      $people->execute([
        ":id" => NULL,
        ":timestamp" => $timestamp,
        ":type" => $type,
        ":title" => $title,
        ":body" => $body,
        ":published" => $published,
      ]);
  
      return true;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
  
  public function update($id_raw, $fields) {
    
    $id = filter_var($id_raw, FILTER_SANITIZE_NUMBER_INT);
    $timestamp = filter_var($fields['timestamp'], FILTER_SANITIZE_STRING);
    $title = htmlentities($fields['title']);
    $body = htmlentities($fields['body']);
    $published = (array_key_exists("published", $fields)) ? 1 : 0;
    
    try {
      $people = $this->app->db->prepare("
    UPDATE `posts` SET
    `timestamp` = :timestamp,
    `title` = :title,
    `body` = :body,
    `published` = :published
    WHERE `id` = :id
    ");
      $people->execute([
        ":id" => $id,
        ":timestamp" => $timestamp,
        ":title" => $title,
        ":body" => $body,
        ":published" => $published,
      ]);
      
      return true;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
  
  public function delete($id) {
    try {
      $people = $this->app->db->prepare("DELETE FROM `posts` WHERE `id` = :id");
      $people->execute([
        ":id" => filter_var($id, FILTER_SANITIZE_NUMBER_INT)
      ]);
      return true;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
}