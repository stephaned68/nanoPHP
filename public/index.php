<?php
session_start();

use framework\App;

define("ROOT_PATH", dirname(__DIR__));
define("MODELS_PATH", ROOT_PATH . "/app/models");
define("VIEWS_PATH", ROOT_PATH . "/app/views");
define("CONTROLLERS_PATH", ROOT_PATH . "/app/controllers");
define("DATA_PATH", ROOT_PATH . DIRECTORY_SEPARATOR . "data");
define("PUBLIC_PATH", ROOT_PATH . "/public");

require ROOT_PATH . "/vendor/autoload.php";

$app = new App();
$app->run();