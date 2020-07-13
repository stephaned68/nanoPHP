<?php

namespace app\migrations;

use framework\BaseMigration;
use framework\MigrationInterface;
use framework\SchemaBuilder;

class DbMigration0000 implements MigrationInterface
{

  public function Execute()
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
    $result = $categories->create(true);
    if (!$result)
      return $result->errorInfo()[2];

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
    $result = $contacts->create(true);
    if (!$result)
      return $result->errorInfo()[2];

    return true;
  }

  public function getDescription()
  {
    return "Création des tables Catégories et Contacts";
  }
}