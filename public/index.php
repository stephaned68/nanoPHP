<?php
session_start();

use framework\Router;
use framework\Dispatcher;

define("ROOT_PATH", dirname(__DIR__));
define("MODELS_PATH", ROOT_PATH . "/app/models");
define("VIEWS_PATH", ROOT_PATH . "/app/views");
define("CONTROLLERS_PATH", ROOT_PATH . "/app/controllers");
define("PUBLIC_PATH", ROOT_PATH . "/public");

if (file_exists("app.conf")) {
  // load database configuration
  $config = parse_ini_file("app.conf");
} else {
  // set default database configuration
  $config = [
    "Database" => [
      "Type" => "mysql",
      "Server" => "localhost",
      "Name" => "simple-fw", // set the full pathname of the .db file for SQLite
      "User" => "root",
      "Password" => ""
    ]
  ];
}

// define the database type
define("DB_TYPE", $config["Database"]["Type"]);

// define the database server
define("DB_SERVER", $config["Database"]["Server"]);

// define the database server port
if (array_key_exists("Port", $config["Database"])) {
  define("DB_PORT", $config["Database"]["Port"]);
} else {
  define("DB_PORT", null);
}

// define the database name or file path
define("DB_NAME", $config["Database"]["Name"]);

// define the database connection string for PDO
switch ($config["Database"]["Type"]) {
  case "mysql":
    $dsn = "mysql:host=" . DB_SERVER . ";port=" . (DB_PORT ?? "3306") . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    break;
  case "pgsql":
    $dsn = "pgsql:host=" . DB_SERVER . ";port=" . (DB_PORT ?? "5432") . ";dbname=" . DB_NAME;
    break;
  case "sqlite":
    $dsn = "sqlite:" . DB_NAME;
    break;
}

// define the database DSN
define("DSN", $dsn);

// define the database user/password
define("DB_USER", $config["Database"]["User"]);
define("DB_PASS", $config["Database"]["Password"]);

// define the application name
define("APP_NAME", $config["Global"]["AppName"] ?? "My Application Name");

// define the Views base layout
define("VIEWS_LAYOUT", "layout");

require ROOT_PATH . "/vendor/autoload.php";

$route = filter_input(INPUT_GET, "route", FILTER_SANITIZE_URL);
if ($route == "") {
  $route = $_SERVER["PATH_INFO"] ?? "";
  $route = substr($route, 1);
}

$router = new Router($route);

$dispatcher = new Dispatcher($router, "app\\controllers\\");
$dispatcher->run();

