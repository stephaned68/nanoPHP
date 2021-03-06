<?php


namespace app\controllers;

use Exception;
use framework\Database;
use framework\Router;
use framework\WebController;

/**
 * Migrations web routes
 * Class MigrationController
 * @package app\controllers3
 */
class MigrationController extends WebController
{

  /**
   * GET /migration/index
   * Run migrations and display result
   * @throws Exception
   */
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