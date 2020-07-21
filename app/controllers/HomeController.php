<?php

namespace app\controllers;

use Exception;
use framework\App;
use framework\Database;

/**
 * Home page routes
 * Class HomeController
 * @package app\controllers
 */
class HomeController extends BaseController
{

  public function indexAction()
  {
    $migrations = [];

    $config = App::loadConfig();
    $migrationsTable = $config["System"]["MigrationsTable"] ?? "";

    if (!empty($migrationsTable)) {
      try {
        if (Database::createMigrationsTable($migrationsTable))
          $migrations = Database::getMigrations();
      } catch (Exception $e) {
      }
    }

    $this->render("home/index", [
      "migrations" => $migrations
    ]);
  }

}