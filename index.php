<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/London');

require "vendor/autoload.php";
require "app/Exceptions.php";
require "app/compose.php";
require "app/controllers/controllers.php";
require "app/objects/objects.php";

use compose\App;

$app = new App;

try {

  $app->router->addRoutes([
    //Blog
    ['GET', '/', 'indexGet'],
    ['GET', '/page/[i:page]', 'pageGet'],
    ['GET', '/post/[i:id]', 'postGet'],
    //Login
    ['GET', '/login', 'loginGet'],
    ['GET', '/logout', 'logoutGet'],
    ['POST', '/login', 'loginPost'],
    //Admin Homepage
    ['GET', '/compose', 'adminHomeGet'],
    ['GET', '/compose/newpost', 'adminNewPostGet'],
    ['POST', '/compose/newpost', 'adminNewPostPost'],

    ['GET', '/compose/post/[i:id]', 'adminEditPostGet'],
    ['GET', '/compose/post/[i:id]/delete', 'adminDeletePostGet'],
    ['POST', '/compose/post/[i:id]', 'adminEditPostPost'],
  
    ['POST', '/dump', 'dumpPost']
  ]);
  
} catch (Exception $e) {
  $app->pageController->errorMessage($e->getMessage());
}

// match current request url
$match = $app->router->match();

// call the mapped pageController method or throw a 404
if (is_array($match)) {
  call_user_func([$app->pageController, $match['target']], $match['params']);
} else {
  $app->pageController->pageNotFound();
}