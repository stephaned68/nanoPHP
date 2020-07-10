<?php

namespace framework;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

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
  private static function hydrate(object $entity, array $data, $isNew = false)
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
}