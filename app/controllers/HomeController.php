<?php

namespace app\controllers;

use app\models\CategoryRepository;
use framework\FormManager;
use framework\SchemaBuilder;

class HomeController extends BaseController
{

  public function indexAction()
  {
    $categories = new SchemaBuilder("categories");
    $categories
      ->identity([
        "name" => "category_id",
        "type" => "int"
      ])
      ->column([
        "name" => "category_name",
        "type" => "varchar",
        "size" => "50",
        "null" => false
      ])
    ;
    $categories->create(true);

    $contacts = new SchemaBuilder("contacts");
    $contacts
      ->identity([
        "name" => "contact_id",
        "type" => "int"
      ])
      ->column([
        "name" => "contact_name",
        "type" => "varchar",
        "size" => "50",
        "null" => false
      ])
      ->column([
        "name" => "contact_email",
        "type" => "varchar",
        "size" => "100",
        "null" => true
      ])
      ->column([
        "name" => "category_id",
        "type" => "int",
        "size" => "11",
        "null" => false
      ])
      ->foreignKey([
        "name" => "fk_contact_category",
        "column" => "category_id",
        "ref" => "category_id",
        "table" => "categories",
        "update" => "CASCADE"
      ])
    ;
    $contacts->create(true);

    $categoryDAO = new CategoryRepository();
    var_dump($categoryDAO->getAll());

    $this->render("home/index");
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