<?php

namespace framework;

use PDO;
use PDOException;

class Database
{

  /**
   * @var PDO $pdo
   */
  private static $pdo = null;

  /**
   * Cannot instantiate -- Singleton
   */
  private function __construct()
  {
  }

  /**
   * Return a PDO connection
   * @return PDO
   */
  public static function getPDO()
  {
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
        Tools::setFlash($ex->getMessage(), "danger");
      }
    }
    return self::$pdo;
  }

  /**
   * Check if a table or view exists in the database
   * @param string $table
   * @return bool
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
    $pdo = Database::getPDO();
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
   * Return the list of columns for a table
   * @param string $table
   * @return array
   */
  public static function getColumnsList(string $table)
  {
    $qb = new QueryBuilder();
    $qb
      ->from("information_schema.columns")
      ->select(["column_name"])
      ->where("table_schema = '" . DB_NAME . "'")
      ->andWhere("table_name = '" . $table . "'");

    $columnsList = [];
    $pdo = Database::getPDO();
    if ($pdo) {
      try {
        $result = $pdo->query($qb->getQuery());
        if ($result) {
          $metaData = $result->fetchAll(PDO::FETCH_ASSOC);
          foreach ($metaData as $meta) {
            $columnsList[] = $meta["column_name"];
          }
        }
      } catch (PDOException $ex) {
      }
    }
    return $columnsList;
  }

}