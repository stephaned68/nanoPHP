<?php

namespace app\controllers;

use framework\Database;
use framework\FormManager;

class HomeController extends BaseController
{

  public function indexAction()
  {
    $migrations = [];

    if (Database::createMigrationsTable()) {
      $migrations = Database::getMigrations();
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

  public function getEdit()
  {
    $fm = $this->getEditForm();

    $this->getView()->setVariable("form", $fm);
    $this->render("home/edit");
  }

  public function postEdit()
  {
    $editForm = $this->getEditForm();
  }

}