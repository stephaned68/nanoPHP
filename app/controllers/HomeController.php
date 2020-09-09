<?php

namespace app\controllers;

use Exception;
use framework\App;
use framework\Database;
use framework\WebController;

/**
 * Home page routes
 * Class HomeController
 * @package app\controllers
 */
class HomeController extends WebController
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