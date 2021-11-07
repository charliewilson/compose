<?php
require_once("vendor/autoload.php");
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable("../");
$dotenv->load();

define('db_host', $_ENV['DB_HOST']);
define('db_name', $_ENV['LC_DB']);
define('db_user', $_ENV['DB_USER']);
define('db_pass', $_ENV['DB_PASS']);