<?php
namespace nano;

use Delight\Auth\AmbiguousUsernameException;
use Delight\Auth\AuthError;
use Delight\Auth\InvalidPasswordException;
use Delight\Auth\AttemptCancelledException;
use Delight\Auth\EmailNotVerifiedException;
use Delight\Auth\TooManyRequestsException;

use Delight\Auth\UnknownUsernameException;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PageController {
  
  private $app;
  
  function __construct(App $app) {
    $this->app = $app;
  }

  public function errorMessage($message) {
    header( $_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error');
    try {
      echo $this->app->twig->render('error.twig', [
        "message" => $message,
        "appData" => $this->app->appData->get()
      ]);
    } catch (LoaderError | RuntimeError | SyntaxError $e) {
      die($e->getMessage());
    }
    die();
  }

  public function pageNotFound() {
    header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    try {
      echo $this->app->twig->render('404.twig', [
        "appData" => $this->app->appData->get()
      ]);
    } catch (LoaderError | RuntimeError | SyntaxError $e) {
      $this->errorMessage($e->getMessage());
    }
    die();
  }

  public function maintenance() {
    header( $_SERVER["SERVER_PROTOCOL"] . ' 503 Service Unavailable');
    try {
      echo $this->app->twig->render('/misc/maintenance.twig', [
        "appData" => $this->app->appData->get()
      ]);
    } catch (LoaderError | RuntimeError | SyntaxError $e) {
      $this->errorMessage($e->getMessage());
    }
    die();
  }
  
  public function dumpPost() {
    try {
      echo $this->app->twig->render('error.twig', [
        "message" => print_r($_POST, true),
        "appData" => $this->app->appData->get()
      ]);
    } catch (LoaderError | RuntimeError | SyntaxError $e) {
      die($e->getMessage());
    }
    die();
  }
  
  //INDEX
  public function indexGet() {
    try {
      $posts = $this->app->postController->getAll();
      $lastPage = ceil(count($posts) / $this->app->appData->get()['postsPerPage']);

      echo $this->app->twig->render('/theme/list.twig', [
        "appData" => $this->app->appData->get(),
        "loggedIn" => $this->app->auth->isLoggedIn(),
        "posts" => $this->app->postController->getPage(1),
        "currentPage" => 1,
        "lastPage" => $lastPage
      ]);
    } catch (LoaderError | RuntimeError | SyntaxError $e) {
      $this->errorMessage($e->getMessage());
    }
    die();
  }

  public function pageGet($params) {
    try {
      $posts = $this->app->postController->getAll();
      $lastPage = ceil(count($posts) / $this->app->appData->get()['postsPerPage']);

      echo $this->app->twig->render('/theme/list.twig', [
        "appData" => $this->app->appData->get(),
        "loggedIn" => $this->app->auth->isLoggedIn(),
        "posts" => $this->app->postController->getPage($params['page']),
        "currentPage" => $params['page'],
        "lastPage" => $lastPage
      ]);
    } catch (LoaderError | RuntimeError | SyntaxError $e) {
      $this->errorMessage($e->getMessage());
    }
    die();
  }
  
  public function postGet($params) {
    try {
      echo $this->app->twig->render('/theme/single.twig', [
        "appData" => $this->app->appData->get(),
        "loggedIn" => $this->app->auth->isLoggedIn(),
        "post" => $this->app->postController->get($params['id'])[0]
      ]);
    } catch (LoaderError | RuntimeError | SyntaxError $e) {
      $this->errorMessage($e->getMessage());
    }
    die();
  }
  
  //LOGIN
  public function loginGet() {
    if ($this->app->auth->isLoggedIn()){
      header("Location: /nano");
    } else {
      //Homepage
      try {
        echo $this->app->twig->render('login/login.twig', [
          "incorrect" => isset($_GET['invalid'])
        ]);
      } catch (LoaderError | RuntimeError | SyntaxError $e) {
        $this->errorMessage($e->getMessage());
      }
      die();
    }
  }
  
  public function logoutGet() {
    if (!$this->app->auth->isLoggedIn()){
      header("Location: /login");
    } else {
      try {
        $this->app->auth->logOut();
        $this->app->auth->destroySession();
      } catch (AuthError $e) {
        $this->errorMessage($e->getMessage());
      }
      header("Location: /");
    }
  }
  
  public function loginPost() {
    if ($this->app->auth->isLoggedIn()) {
      header("Location: /nano");
    } else {
      $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
      $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
      try {
        $this->app->auth->loginWithUsername($username, $password, (int)(60 * 60 * 24 * 365.25));
        header("Location: /nano");
      } catch (UnknownUsernameException | InvalidPasswordException $e) {
        header("Location: /login?invalid");
      } catch (
      AttemptCancelledException |
      EmailNotVerifiedException |
      TooManyRequestsException|
      AmbiguousUsernameException|
      AuthError $e) {
        $this->errorMessage($e->getMessage());
      }
    }
  }
  
  //NANO
  public function adminHomeGet() {
    if ($this->app->auth->isLoggedIn()){
      try {
        echo $this->app->twig->render('admin/home.twig', [
          "me" => $this->app->auth->getUsername(),
          "posts" => $this->app->postController->getAll(["ofType" => "post", "includeDrafts" => true]),
          "photos" => $this->app->postController->getAll(["ofType" => "photo", "includeDrafts" => true])
        ]);
      } catch (LoaderError | RuntimeError | SyntaxError $e) {
        $this->errorMessage($e->getMessage());
      }
      die();
    } else {
      header("Location: /login");
    }
  }
  
  public function adminNewPostGet() {
    if ($this->app->auth->isLoggedIn()){
      try {
        echo $this->app->twig->render('admin/newpost.twig', [
          "me" => $this->app->auth->getUsername(),
          "timestamp" => date("Y-m-d H:i")
        ]);
      } catch (LoaderError | RuntimeError | SyntaxError $e) {
        $this->errorMessage($e->getMessage());
      }
      die();
    } else {
      header("Location: /login");
    }
  }
  
  public function adminNewPhotoGet() {
    if ($this->app->auth->isLoggedIn()){
      try {
        echo $this->app->twig->render('admin/newphoto.twig', [
          "me" => $this->app->auth->getUsername(),
          "timestamp" => date("Y-m-d H:i")
        ]);
      } catch (LoaderError | RuntimeError | SyntaxError $e) {
        $this->errorMessage($e->getMessage());
      }
      die();
    } else {
      header("Location: /login");
    }
  }
  
  public function adminNewPhotoPost() {
    if ($this->app->auth->isLoggedIn()){
      
      $response = $this->app->postController->create($_POST);
      
      if ($response === true) {
        header("Location: /nano");
      } else {
        $this->app->pageController->errorMessage($response);
      }
    } else {
      header("Location: /login");
    }
  }
  
  public function adminEditPhotoGet($params) {
    if ($this->app->auth->isLoggedIn()){
      try {
        echo $this->app->twig->render('admin/editphoto.twig', [
          "me" => $this->app->auth->getUsername(),
          "photo" => $this->app->postController->get(filter_var($params['id'], FILTER_SANITIZE_NUMBER_INT), ["asType" => "photo"])[0]
        ]);
      } catch (LoaderError | RuntimeError | SyntaxError $e) {
        $this->errorMessage($e->getMessage());
      }
      die();
    } else {
      header("Location: /login");
    }
  }
  
  public function adminEditPhotoPost($params) {
    if ($this->app->auth->isLoggedIn()){
      
      $response = $this->app->postController->update(filter_var($params['id'], FILTER_SANITIZE_NUMBER_INT),$_POST);
      
      if ($response === true) {
        header("Location: /nano");
      } else {
        $this->app->pageController->errorMessage($response);
      }
    } else {
      header("Location: /login");
    }
  }
  
  public function adminNewPostPost() {
    if ($this->app->auth->isLoggedIn()){
      
      $response = $this->app->postController->create($_POST);
      
      if ($response === true) {
        header("Location: /nano");
      } else {
        $this->app->pageController->errorMessage($response);
      }
    } else {
      header("Location: /login");
    }
  }
  
  public function adminEditPostGet($params) {
    if ($this->app->auth->isLoggedIn()){
      try {
        echo $this->app->twig->render('admin/editpost.twig', [
          "me" => $this->app->auth->getUsername(),
          "post" => $this->app->postController->get(filter_var($params['id'], FILTER_SANITIZE_NUMBER_INT))[0]
        ]);
      } catch (LoaderError | RuntimeError | SyntaxError $e) {
        $this->errorMessage($e->getMessage());
      }
      die();
    } else {
      header("Location: /login");
    }
  }
  
  public function adminEditPostPost($params) {
    if ($this->app->auth->isLoggedIn()){
      
      $response = $this->app->postController->update(filter_var($params['id'], FILTER_SANITIZE_NUMBER_INT),$_POST);
      
      if ($response === true) {
        header("Location: /nano");
      } else {
        $this->app->pageController->errorMessage($response);
      }
    } else {
      header("Location: /login");
    }
  }
  
  public function adminDeletePostGet($params) {
    if ($this->app->auth->isLoggedIn()){
      
      $response = $this->app->postController->delete(filter_var($params['id'], FILTER_SANITIZE_NUMBER_INT));
      
      if ($response === true) {
        header("Location: /nano");
      } else {
        $this->app->pageController->errorMessage($response);
      }
    } else {
      header("Location: /login");
    }
  }
}