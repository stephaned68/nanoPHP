<?php

namespace framework;

use Exception;
use PDO;
use PDOException;
use PDOStatement;


/**
 * Database utility functions
 * Class Database
 * @package framework
 */
class Database
{

  /**
   * @var PDO $pdo
   */
  private static ?PDO $pdo = null;

  /**
   * Cannot instantiate -- Singleton
   */
  private function __construct()
  {
  }

  /**
   * Return a PDO connection
   * @return PDO
   * @throws Exception
   */
  public static function getPDO()
  {
    if (!in_array(DB_TYPE, PDO::getAvailableDrivers(), true))
    {
      throw new Exception(DB_TYPE . " non disponible");
    }
    if (!self::$pdo) {
      try {
        self::$pdo = new PDO(
          DSN,
          DB_USER,
          DB_PASS,
          [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
          ]
        );
      } catch (PDOException $ex) {
        throw new Exception("Connexion impossible", 0, $ex);
      }
    }
    return self::$pdo;
  }

  /**
   * Check if a table or view exists in the database
   * @param string $table
   * @return bool
   * @throws Exception
   */
  public static function exists(string $table)
  {
    $qb = new QueryBuilder();
    $qb
      ->from("information_schema.tables")
      ->select(["count(*) as found"])
      ->where("table_schema = '" . DB_NAME . "'")
      ->andWhere("table_name = '" . $table . "'");

    $exists = false;
    $pdo = self::getPDO();
    if ($pdo) {
      try {
        $result = $pdo->query($qb->getQuery())->fetch();
        $exists = ($result["found"] > 0);
      } catch (PDOException $ex) {
      }
    }
    return $exists;

  }

  /**
   * Return table name for entity
   * @param string $entityName
   * @return string
   */
  public static function tableName(string $entityName)
  {
    return Tools::snakeCase(Tools::pluralize($entityName));
  }

  /**
   * Return column name for attribute
   * @param string $attributeName
   * @return string
   */
  public static function columnName(string $attributeName)
  {
    return Tools::snakeCase($attributeName);
  }

  /**
   * Prepare and execute a SQL query with optional named parameters
   * @param string $sqlQuery
   * @param array $queryParams
   * @return PDOStatement
   * @throws Exception
   */
  public static function execute(string $sqlQuery, array $queryParams = []) : PDOStatement
  {
    $statement = self::getPDO()->prepare($sqlQuery);
    if (count($queryParams) > 0) {
      foreach ($queryParams as $key => $value) {
        if ($value != null)
          $statement->bindValue($key, $value);
      }
    }
    $statement->execute();
    return $statement;
  }

  /**
   * Hydrate an entity object from a row of data
   * @param object $entity
   * @param array $data
   * @param bool $isNew
   * @return object
   */
  public static function hydrate(object $entity, array $data, $isNew = false)
  {
    $data["new"] = $isNew;

    foreach($data as $key => $value)
    {
      Tools::setProperty($entity, Tools::pascalize($key), $value);
    }

    return $entity;
  }

  /**
   * Get a single entity
   * @param PDOStatement $stmt
   * @param string $className
   * @return object
   */
  public static function fetchEntity(PDOStatement $stmt, string $className) : ?object
  {
    $row = $stmt->fetch();
    if ($row) {
      return self::hydrate(new $className(), $row);
    }
    else {
      return null;
    }
  }

  /**
   * Get a list of entities
   * @param PDOStatement $stmt
   * @param string $className
   * @return array
   */
  public static function fetchEntities(PDOStatement $stmt, string $className) : array
  {
    $entities = [];
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
      $entities[] = self::hydrate(new $className(), $row);
    }
    return $entities;
  }

  /**
   * Convert an entity object to an associative array
   * @param object $entity
   * @return array
   */
  public static function entityToArray(object $entity) : array
  {
    $data = [];
    foreach (get_class_methods($entity) as $accessor)
    {
      if (substr($accessor, 0, 3) == "get") {
        $column = Tools::snakeCase(substr($accessor, 3));
        $data[$column] = $entity->$accessor();
      }
    }
    return $data;
  }

  /**
   * Create the database migrations table
   * @param string $table
   * @return bool|PDOStatement
   * @throws Exception
   */
  public static function createMigrationsTable(string $table)
  {
    $migrations = new SchemaBuilder($table);
    $migrations
      ->identity([
        "name" => "migration_id",
        "type" => "int"
      ])
      ->column([
        "name" => "migration_name",
        "type" => "varchar",
        "size" => "250",
        "null" => false
      ])
      ->column([
        "name" => "description",
        "type" => "text",
        "null" => true
      ])
      ->column([
        "name" => "executed_at",
        "type" => "datetime",
        "null" => true
      ])
    ;
    return $migrations->create(true);
  }

  /**
   * Get a list of pending db migrations
   * @param string|null $migrationsDir
   * @return array|void
   * @throws Exception
   */
  public static function getMigrations(string $migrationsDir = null)
  {
    if ($migrationsDir == null)
      $migrationsDir = ROOT_PATH . "/data/migrations";
    if (!file_exists($migrationsDir))
      return;
    if (!is_dir($migrationsDir))
      return;

    $migrationsList = [];

    $migrations = scandir($migrationsDir);
    if (count($migrations) > 0) {
      $config = App::loadConfig();

      // build the list of executed migrations
      $executedMigrations = [];
      $qb = new QueryBuilder($config["System"]["MigrationsTable"]);
      $stmt = $qb
        ->select(["migration_name"])
        ->where("executed_at IS NOT NULL")
        ->execute();
      $qb = null;
      foreach ($stmt->fetchAll() as $migration) {
        $executedMigrations[] = $migration["migration_name"];
      }

      // build the list of migrations to execute
      $migrationsList = [];
      foreach ($migrations as $migration) {
        if (substr($migration, -4) == ".php") {
          if (!in_array($migration, $executedMigrations)) {
            $migrationClass = "app\\migrations\\" . str_replace(".php", "", $migration);
            $migrationsList[$migration] = new $migrationClass();
          }
        }
      }
    }
    return $migrationsList;
  }

  /**
   * Execute a list of db migrations
   * @param array $migrationsList
   * @return array List of migrations results
   * @throws Exception
   */
  public static function migrate(array $migrationsList = [])
  {
    if (count($migrationsList) == 0)
      $migrationsList = Database::getMigrations();

    $migrations = [];

    $config = App::loadConfig();

    foreach ($migrationsList as $migrationName => $migrationInstance) {
      $migration = $migrationInstance->getDescription();
      $result = $migrationInstance->execute();
      if ($result == true) {
        $qb = new QueryBuilder($config["System"]["MigrationsTable"]);
        $qb->insert([
          "migration_name" => $migrationName,
          "description" => $migrationInstance->getDescription(),
          "executed_at" => date("Y-m-d")
        ])->commit();
        $qb = null;
        $migration .= " : Ok";
      }
      else {
        $migration .= " : " . $result;
      }
      $migrations[] = $migration;
    }

    return $migrations;
  }
}