<?php

namespace app\controllers;

use Exception;
use framework\App;
use framework\Database;
use framework\FormManager;

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

  private function getEditForm()
  {
    $editForm = new FormManager();
    $editForm
      ->setTitle("Maintenance des Contacts")
      ->addField([
        "name" => "contactId",
        "label" => "Id. du contact",
        "primeKey" => true
      ])
      ->addField([
        "name" => "contactName",
        "label" => "Nom du contact",
        "required" => true
      ])
      ->addField([
        "name" => "contactEmail",
        "label" => "Email du contact"
      ])
      ->addField([
        "name" => "categoryId",
        "label" => "Catégorie de contact",
        "required" => true
      ])
    ;
    return $editForm;
  }

}