<?php


namespace app\controllers;


use framework\Database;
use framework\Router;

class MigrationController extends BaseController
{

  public function indexAction()
  {
    $pendingMigrations = Database::getMigrations();

    if (count($pendingMigrations) == 0) {
      Router::redirectTo([ "home" ]);
      return;
    }

    $migrationList = Database::migrate($pendingMigrations);

    $this->render("migration/index", [
      "migrationList" => $migrationList
    ]);
  }

}