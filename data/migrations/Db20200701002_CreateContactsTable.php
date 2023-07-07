<?php


namespace app\migrations;


use framework\MigrationInterface;
use framework\SchemaBuilder;

/**
 * Create Contacts table
 * Class Db20200701002_CreateContactsTable
 * @package app\migrations
 */
class Db20200701002_CreateContactsTable implements MigrationInterface
{

  /**
   * Get migration description
   * @return string
   */
  public function getDescription() : string
  {
    return "Création de la table Contacts";
  }

  /**
   * Execute migration
   * @return bool
   * @throws \Exception
   */
  public function execute() : mixed
  {
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
      ->hasCreatedAt()
      ->hasUpdatedAt()
    ;
    $result = $contacts->create(true);
    if (!$result)
      return $result->errorInfo()[2];

    return true;
  }
}