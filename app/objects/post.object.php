<?php
namespace compose;
use Parsedown;
use PDO;

class Post {
  
  private $app;
  
  private $id;
  private $timestamp;
  private $type;
  private $title;
  private $body;
  private $published;
  
  function __construct(PDO $dbInstance = null) {
    $this->app = new App($dbInstance);
  }
  
  public function id() {
    return $this->id;
  }
  
  public function timestamp() {
    return $this->timestamp;
  }
  
  public function type() {
    return $this->type;
  }
  
  public function title() {
    return html_entity_decode($this->title);
  }
  
  public function body() {
    return [
      "markdown" => html_entity_decode($this->body),
      "html" => Parsedown::instance()->text(html_entity_decode($this->body))
    ];
  }
  
  public function published() {
    return ($this->published == 1) ? true : false;
  }
  
}