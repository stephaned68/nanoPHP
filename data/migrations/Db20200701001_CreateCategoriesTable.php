<?php

namespace app\migrations;

use framework\MigrationInterface;
use framework\SchemaBuilder;

/**
 * Create Categories table
 * Class Db20200701001_CreateCategoriesTable
 * @package app\migrations
 */
class Db20200701001_CreateCategoriesTable implements MigrationInterface
{

  /**
   * Get migration description
   * @return string
   */
  public function getDescription() : string
  {
    return "Création de la table Catégories";
  }

  /**
   * Execute migration
   * @return bool|mixed
   */
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

    return true;
  }

}