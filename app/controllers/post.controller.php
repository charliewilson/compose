<?php
namespace compose;

use DateTime;
use Intervention\Image\ImageManagerStatic as Image;
use PDO;
use PDOException;

class PostController {
  
  private $app;
  
  function __construct(App $app) {
    $this->app = $app;
  }
  
  public function getAll($options = []) {
    
    $defaultOptions = [
      "ofType" => "all",
      "includeDrafts" => false
    ];
    foreach($options as $option=>$value) {
      if (isset($defaultOptions[$option])) {
        $defaultOptions[$option] = $value;
      }
    }
  
    switch ($defaultOptions['ofType']) {
      default:
        if ($defaultOptions["includeDrafts"] == true) {
          $statement = "SELECT * FROM `posts` ORDER BY `timestamp` DESC";
        } else {
          $statement = "SELECT * FROM `posts` WHERE published = 1 ORDER BY `timestamp` DESC";
        }
        break;
    }
    
    $people = $this->app->db->prepare($statement);
    $people->execute();
  
  
    switch ($defaultOptions['ofType']) {
      default:
        return $people->fetchAll(PDO::FETCH_CLASS,'\compose\Post', [
          $this->app->db
        ]);
        break;
    }
    
  }

  public function getPage($page = 1, $options = []) {

    $defaultOptions = [
      "ofType" => "all",
      "includeDrafts" => false
    ];
    foreach($options as $option=>$value) {
      if (isset($defaultOptions[$option])) {
        $defaultOptions[$option] = $value;
      }
    }

    switch ($defaultOptions['ofType']) {
      default:
        if ($defaultOptions["includeDrafts"] == true) {
          $statement = "SELECT * FROM `posts` ORDER BY `timestamp` DESC LIMIT :offset, :limit";
        } else {
          $statement = "SELECT * FROM `posts` WHERE published = 1 ORDER BY `timestamp` DESC LIMIT :offset, :limit";
        }
        break;
    }

    $people = $this->app->db->prepare($statement);
    $people->execute([
      ":offset" => ($page - 1) * $this->app->appData->get()['postsPerPage'],
      ":limit" => $this->app->appData->get()['postsPerPage']
    ]);


    switch ($defaultOptions['ofType']) {
      default:
        return $people->fetchAll(PDO::FETCH_CLASS,'\compose\Post', [
          $this->app->db
        ]);
        break;
    }

  }
  
  public function get($id, $options = []) {
    
    $defaultOptions = [
      "asType" => "post"
    ];
    foreach($options as $option=>$value) {
      if (isset($defaultOptions[$option])) {
        $defaultOptions[$option] = $value;
      }
    }
    
    $post = $this->app->db->prepare("SELECT * FROM `posts` WHERE `id` = :id");
    $post->execute([
      ":id" => filter_var($id, FILTER_SANITIZE_NUMBER_INT)
    ]);

    switch ($defaultOptions['asType']) {
      default:
        return $post->fetchAll(PDO::FETCH_CLASS,'\compose\Post', [
          $this->app->db
        ]);
        break;
    }
  
  }
  
  public function create($fields) {

    $timestamp = filter_var($fields['timestamp'], FILTER_SANITIZE_STRING);
    $published = (array_key_exists("published", $fields)) ? 1 : 0;
    

    $type = "post";
    $title = htmlentities($fields['title']);
    $body = htmlentities($fields['body']);

  
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