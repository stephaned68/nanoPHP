<?php


namespace framework;

use Exception;
use PDOStatement;

/**
 * Fluent methods to manage db schema
 * (create/alter/drop db objects)
 * Class SchemaBuilder
 * @package framework
 */
class SchemaBuilder
{

  /**
   * @var string
   */
  private string $table;

  /**
   * @var array
   */
  private array $columns = [];

  /**
   * @var array
   */
  private array $primaryKeys = [];

  /**
   * @var array
   */
  private array $foreignKeys = [];

  /**
   * @var bool
   */
  protected bool $createdAt = false;

  /**
   * @var bool
   */
  protected bool $updatedAt = false;

  /**
   * SchemaBuilder constructor.
   * @param string $table
   */
  public function __construct(string $table)
  {
    $this->table = $table;
  }

  /**
   * @return SchemaBuilder
   */
  public function hasCreatedAt(): SchemaBuilder
  {
    $this->createdAt = true;
    return $this;
  }

  /**
   * @return SchemaBuilder
   */
  public function hasUpdatedAt(): SchemaBuilder
  {
    $this->updatedAt = true;
    return $this;
  }

  /**
   * Add an identity column
   * @param array $columnInfo
   * @return $this
   */
  public function identity(array $columnInfo) : SchemaBuilder
  {
    switch (DB_TYPE) {
      case "mysql":
        $this->columns[] = [
          "name" => $columnInfo["name"],
          "type" => $columnInfo["type"],
          "null" => false,
          "extra" => "AUTO_INCREMENT"
        ];
        $this->primaryKeys[] = $columnInfo["name"];
        break;
      case "pgsql":
        $size = "";
        if (strtolower($columnInfo["type"]) == "tinyint" || strtolower($columnInfo["type"] == "smallint")) {
          $size = "SMALL";
        } else if (strtolower($columnInfo["type"]) == "bigint") {
          $size = "BIG";
        }
        $this->columns[] = [
          "name" => $columnInfo["name"],
          "type" => "${size}SERIAL",
        ];
        $this->primaryKeys[] = $columnInfo["name"];
        break;
      case "sqlite":
        $this->columns[] = [
          "name" => $columnInfo["name"],
          "type" => "INTEGER PRIMARY KEY",
        ];
        break;
    }
    return $this;
  }

  /**
   * Add a column
   * @param array $columnInfo
   * @return $this
   */
  public function column(array $columnInfo) : SchemaBuilder
  {
    $this->columns[] = $columnInfo;
    return $this;
  }

  /**
   * Add a primary key segment
   * @param string $column
   * @return $this
   */
  public function primaryKey(string $column) : SchemaBuilder
  {
    $this->primaryKeys[] = $column;
    return $this;
  }

  /**
   * Add a foreign key constraint
   * @param array $fkInfo
   * @return $this
   */
  public function foreignKey(array $fkInfo) : SchemaBuilder
  {
    $this->foreignKeys[] = $fkInfo;
    return $this;
  }

  /**
   * Return quoted identifier
   * @param string $name
   * @return string
   */
  private function name(string $name) : string
  {
    $delimiter = "";
    switch (DB_TYPE) {
      case "mysql":
        $delimiter = "`";
        break;
      case "pgsql":
        $delimiter = '"';
        break;
    }
    return $delimiter.$name.$delimiter;
  }

  /**
   * Return type and size based on DB back-end
   * @param array $columnInfo
   * @return string
   */
  public function type(array $columnInfo) : string
  {
    $type = strtoupper($columnInfo["type"]);
    switch (DB_TYPE) {
      case "mysql":
      case "sqlite":
        if (array_key_exists("size", $columnInfo)) {
          $type .= "(" . $columnInfo["size"] . ")";
        }
        break;
      case "pgsql":
        if (in_array($type, [ "BIT", "CHAR", "VARCHAR", "DECIMAL" ])) {
          $type .= "(" . $columnInfo["size"] . ")";
        }
        break;
    }
    return $type;
  }

  /**
   * Create the table in the database
   * @param bool $ifNotExist
   * @return PDOStatement
   * @throws Exception
   */
  public function create(bool $ifNotExist = true): PDOStatement
  {
    $sql = [];
    $sql[] = "CREATE TABLE " . ($ifNotExist ? "IF NOT EXISTS " : "") . $this->name($this->table);
    $sql[] = "(";
    foreach ($this->columns as $ix => $columnInfo)
    {
      $columnDef = $ix == 0 ? "" : ", ";
      $columnDef .= $this->name($columnInfo["name"]);
      $columnDef .= " " . $this->type($columnInfo);
      if (array_key_exists("null", $columnInfo)) {
        $columnDef .= ($columnInfo["null"] ? "" : " NOT") . " NULL";
      }
      if (array_key_exists("unique", $columnInfo)) {
        $columnDef .= ($columnInfo["unique"] ? " UNIQUE" : "");
      }
      if (array_key_exists("extra", $columnInfo)) {
        $columnDef .= " " . $columnInfo["extra"];
      }
      $sql[] = $columnDef;
    }
    if ($this->createdAt) {
      $sql[] = ", created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
    }
    if ($this->updatedAt) {
      $sql[] = ", updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    }
    if (count($this->primaryKeys) > 0) {
      $sql[] = ", PRIMARY KEY (" . implode(", ", $this->primaryKeys) . ")";
    }
    foreach ($this->foreignKeys as $foreignKey) {
      $fkDef = "";
      if (array_key_exists("name", $foreignKey)) {
        $fkDef .= "CONSTRAINT " . $foreignKey["name"] . " ";
      }
      $fkDef .= "FOREIGN KEY (" . $foreignKey["column"] .") ";
      $fkDef .= "REFERENCES " . $foreignKey["table"] . "(" . $foreignKey["ref"] . ")";
      if (array_key_exists("update", $foreignKey)) {
        $fkDef .= " ON UPDATE " . $foreignKey["update"];
      }
      if (array_key_exists("delete", $foreignKey)) {
        $fkDef .= " ON DELETE " . $foreignKey["delete"];
      }
      $sql[] = ", " . $fkDef;
    }
    $sql[] = ")";
    if (DB_TYPE == "mysql") {
      $sql[] = "ENGINE=InnoDB DEFAULT CHARSET=utf8";
    }

    $sqlQuery = implode(" ", $sql);
    return Database::execute($sqlQuery);
  }
}