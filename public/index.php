<?php
session_start();

use framework\App;

/**
 * Setup global variables
 */
define("ROOT_PATH", dirname(__DIR__));
define("MODELS_PATH", ROOT_PATH . "/app/models");
define("VIEWS_PATH", ROOT_PATH . "/app/views");
define("CONTROLLERS_PATH", ROOT_PATH . "/app/controllers");
define("CONFIG_PATH", ROOT_PATH . "/config");
define("DATA_PATH", ROOT_PATH . "/data");
define("PUBLIC_PATH", ROOT_PATH . "/public");

/**
 * Register autoloader
 */
require ROOT_PATH . "/vendor/autoload.php";

/**
 * Run the application
 */
$app = new App();
$app->run();