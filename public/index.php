<?php
session_start();

use framework\App;

/**
 * Setup global variables
 */
define("ROOT_PATH", dirname(__DIR__));
const MODELS_PATH = ROOT_PATH . "/app/models";
const VIEWS_PATH = ROOT_PATH . "/app/views";
const COMPONENTS_PATH = ROOT_PATH . "/app/components";
const CONTROLLERS_PATH = ROOT_PATH . "/app/controllers";
const CONFIG_PATH = ROOT_PATH . "/config";
const DATA_PATH = ROOT_PATH . "/data";
const PUBLIC_PATH = ROOT_PATH . "/public";

/**
 * Register autoloader
 */
require ROOT_PATH . "/vendor/autoload.php";

/**
 * Run the application
 */
$app = new App();
try {
  $app->run();
} catch (Exception $e) {
  die($e->getMessage());
}