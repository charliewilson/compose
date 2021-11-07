<?php

namespace nano;

require_once("vendor/autoload.php");
require_once("config.php");
//require_once("exceptions.php");
require_once("objects/objects.php");
require_once("controllers/controllers.php");

use PDO;

use AltoRouter;
use Exception;

use Delight\Auth\Auth;

use Dotenv\Dotenv;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Twig\Extra\Intl\IntlExtension;

class App {

  public $db;
  public $router;
  public $twig;
  public $auth;
  public $user;
  public $appData;
  
  public $pageController;
  public $postController;
  
  function __construct(PDO $db = null) {
    //Load environment variables
    Dotenv::createImmutable(__DIR__."/../..")->load();

    //If a DB instance has been passed (nested App objects), reuse the connection. Otherwise create
    //a new one.
    
//    die(__DIR__);
    
    $this->db = ($db) ? $db : new PDO('sqlite:'.__DIR__.'/../../nano.db');

    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->router = new AltoRouter();
    $this->twig = new Environment((new FilesystemLoader('app/views')),[
      'debug' => true
    ]);
    $this->twig->addExtension(new DebugExtension());
    $this->twig->addExtension(new IntlExtension());
    $this->auth = new Auth($this->db);
    $this->user = new User($this->db);
    $this->appData = new appData;
    
    $this->pageController = new PageController($this);
    $this->postController = new PostController($this);
  }

}

class AppData {

  public $appName, $version;

  function __construct() {
    $this->appName = 'nano';
    $this->version = '1.0';
  }

  public function get() {
    return [
      "name" => $this->appName,
      "version" => $this->version,
      "year" => date("Y")
    ];
  }

}

class User {

  private $db;

  function __construct(PDO $db) {
    $this->db = $db;
  }

  // Confirms that the email exists and password is correct.
  // Returns true if correct, false in any other case.
  public function confirmDetails($email, $pass) {
    try {
      $q = $this->db->prepare("
        SELECT `email`, `password`
        FROM `users`
        WHERE `email` = :email
      ");

      $q->execute([
        ':email' => filter_var($email,FILTER_SANITIZE_EMAIL)
      ]);

      if ($q) {
        $data = $q->fetch();
        return (password_verify(filter_var($pass, FILTER_SANITIZE_STRING), $data['password'])) ? true : false;
      } else {
        return false;
      }
    } catch (Exception $e) {
      return false;
    }

  }
}