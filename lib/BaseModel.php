<?php

namespace framework;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

abstract class BaseModel
{
  protected bool $new = true;

  /**
   * BaseModel constructor.
   * @param array $data
   */
  public function __construct($data = [])
  {
    if (count($data)) {
      self::hydrate($this, $data);
    }
  }

  /**
   * @return bool
   */
  public function isNew(): bool
  {
    return $this->new;
  }

  /**
   * @param bool $new
   * @return BaseModel
   */
  public function setNew(bool $new): BaseModel
  {
    $this->new = $new;
    return $this;
  }

  /**
   * @var PDO Database connection
   */
  protected static ?PDO $db = null;

  /**
   * @var string Entity name
   */
  protected static string $entity;

  /**
   * @var string Table name
   */
  protected static string $table;

  /**
   * @var array Table columns list
   */
  protected static array $columnList = [];

  /**
   * @var array Primary key columns list
   */
  protected static array $pkColumns = [];

  /**
   * @var array Columns mapping [column => attribute]
   */
  protected static array $columnsMap = [];

  /**
   * @var array Attributes mapping [attribute => column]
   */
  protected static array $attributesMap = [];

  /**
   * Initialize the entity configuration settings
   * @param string $entity
   * @param array $configuration
   * @throws Exception
   */
  protected static function initialize(string $entity, array $configuration = [])
  {
    self::$db = Database::getPDO();
    if (!self::$db) {
      throw new Exception("Cannot access database");
    }

    /**
     * initialize entity name
     * e.g. Contact
     */
    self::$entity = $entity;

    /**
     * initialize table name
     * e.g. Contact -> contacts
     */
    self::$table = Tools::pluralize(strtolower($entity));
    self::$table = $configuration["table"] ?? self::$table;

    /**
     * initialize primary key columns
     * e.g. Contact -> contact_id
     */
    self::$pkColumns = [strtolower($entity) . "_id"];
    self::$pkColumns = $configuration["pkColumns"] ?? self::$pkColumns;

    /**
     * initialize columns list and mappings
     * e.g.
     * Table  : contacts   | Class     : Contact
     * Column : contact_id | Attribute : contactId
     */
    if (Database::exists(self::$table)) {
      self::$columnList = Database::getColumnsList(self::$table);
      foreach (self::$columnList as $column) {
        $attribute = Tools::camelize($column);
        self::$columnsMap[$column] = $attribute;
        self::$attributesMap[$attribute] = $column;
      }
    }
    if (isset($configuration["columnsMap"])) {
      self::$columnsMap = $configuration["columnsMap"];
      self::$attributesMap = [];
      foreach (self::$columnsMap as $mapKey => $mapValue) {
        self::$attributesMap[$mapValue] = $mapKey;
      }
    }
  }

  /**
   * Configure model
   * @return mixed
   */
  abstract public static function configure();

  /**
   * @return string
   */
  public static function getEntity(): string
  {
    return self::$entity;
  }

  /**
   * @return string
   */
  public static function getTable(): string
  {
    return self::$table;
  }

  /**
   * @return array
   */
  public static function getColumnList(): array
  {
    return self::$columnList;
  }

  /**
   * @return array
   */
  public static function getPkColumns(): array
  {
    return self::$pkColumns;
  }

  /**
   * @return array
   */
  public static function getColumnsMap(): array
  {
    return self::$columnsMap;
  }

  /**
   * @return array
   */
  public static function getAttributesMap(): array
  {
    return self::$attributesMap;
  }

  /**
   * Add the SQL WHERE clause for primary key(s)
   * @param array $sql
   * @return array
   */
  private static function where(array $sql)
  {
    $sql[] = "where " . self::$pkColumns[0] . " = :" . self::$pkColumns[0];
    if (count(self::$pkColumns) > 1) {
      for ($pk = 1; $pk < count(self::$pkColumns); $pk++) {
        $sql[] = "and " . self::$pkColumns[$pk] . " = :" . self::$pkColumns[$pk];
      }
    }
    return $sql;
  }

  /**
   * Execute SQL query and return results
   * @param array $sql
   * @return false|PDOStatement
   */
  private static function query(array $sql)
  {
    return self::$db->query(implode(" ", $sql));
  }

  /**
   * Prepare and return a PDO statement
   * @param array $sql
   * @return bool|PDOStatement
   */
  private static function prepare(array $sql)
  {
    return self::$db->prepare(implode(" ", $sql));
  }

  /**
   * Return an associative array of fields => values
   * @param array $fields
   * @param array $values
   * @return array
   */
  private static function data(array $fields, array $values)
  {
    $data = [];
    for ($f = 0; $f < count($fields); $f++) {
      $data[":" . $fields[$f]] = $values[$f];
    }
    return $data;
  }

  /**
   * Hydrate an entity object from an assoc. array
   * (either a row read from table or $_POST form data)
   * @param object $entity
   * @param array $data
   * @return mixed
   */
  private static function hydrate(object $entity, array $data)
  {
    foreach (self::$columnsMap as $column => $attribute) {
      $setter = "set" . ucfirst($attribute);
      if (method_exists($entity, $setter)) {
        if (array_key_exists($column, $data)) {
          $entity->$setter($data[$column]);
        }
        if (array_key_exists($attribute, $data)) {
          $entity->$setter($data[$attribute]);
        }
      }
    }
    return $entity;
  }

  /**
   * @param array $data
   * @return mixed
   */
  public static function load(array $data)
  {
    $class = "app\\models\\" . self::$entity;
    return new $class($data);
  }

  /**
   * CRUD+ : Return all as an array of entities
   * @return array|string
   */
  public static function findAll()
  {
    $sql = [
      "select * from",
      self::$table,
    ];

    $result = [];
    try {
      $statement = self::query($sql);
      if ($statement instanceof PDOStatement) {
        $rows = $statement->fetchAll();
        foreach ($rows as $row) {
          $entity = self::load($row);
          $entity->setNew(false);
          $result[] = $entity;
        }
      }
    } catch (PDOException $ex) {
      $result = $ex->getMessage();
    }

    return $result;
  }

  /**
   * CRUD+ : Return an entity
   * @param $pkValues
   * @return mixed|string|null
   */
  public static function findOne($pkValues)
  {
    if (!is_array($pkValues)) {
      $pkValues = [$pkValues];
    }

    $sql = [
      "select * from",
      self::$table,
    ];
    $sql = self::where($sql);

    $result = null;
    $statement = self::prepare($sql);

    if ($statement instanceof PDOStatement) {
      $data = self::data(self::$pkColumns, $pkValues);
      try {
        $success = $statement->execute($data);
        if ($success) {
          $result = self::load($statement->fetch());
          $result->setNew(false);
        }
      } catch (PDOException $ex) {
        $result = $ex->getMessage();
      }
    }

    return $result;
  }

  /**
   * @param $name
   * @param $arguments
   * @return mixed|null
   */
  public static function __callStatic($name, $arguments)
  {
    $result = null;

    $findBy = strpos($name, "findBy");
    if ($findBy == 0) {
      $column = explode("_", Tools::snakeCase($name))[2];
      $qb = new QueryBuilder(self::$table);
      $qb
        ->where("$column = ?")
        ->select();
      $statement = self::$db->prepare($qb->getQuery());
      $success = $statement->execute($arguments);
      if ($success) {
        $data = $statement->fetch();
        $result = self::load($data);
        $result->setNew(false);
      }
    }

    return $result;
  }

  /**
   * CRUD+ : Insert the current entity in the database
   * @return bool|string
   */
  public function insert()
  {
    $sql = [
      "insert into",
      self::$table,
      "(" . implode(", ", self::$columnList) . ")",
    ];
    $columns = "";
    $data = [];
    foreach (self::$columnList as $column) {
      $columns .= ", :$column";
      $attribute = self::$columnsMap[$column];
      $data[":$column"] = $this->$attribute;
    }

    $sql[] = "values (" . substr($columns, 2) . ")";

    $statement = self::prepare($sql);
    if ($statement instanceof PDOStatement) {
      try {
        $success = $statement->execute($data);
      } catch (PDOException $ex) {
        $success = $ex->getMessage();
      }
    } else {
      $success = false;
    }
    return $success;
  }

  /**
   * CRUD+ : Update the current entity in the database
   * @return bool|string
   */
  public function update()
  {
    $sql = [
      "update",
      self::$table,
      "set"
    ];

    $columns = [];
    $data = [];
    foreach (self::$columnList as $column) {
      if (!in_array($column, self::$pkColumns)) {
        $columns[] = "$column = :$column";
      }
      $attribute = self::$columnsMap[$column];
      $data[":$column"] = $this->$attribute;
    }

    $sql[] = implode(", ", $columns);
    $sql = self::where($sql);

    $statement = self::prepare($sql);
    if ($statement instanceof PDOStatement) {
      try {
        $success = $statement->execute($data);
      } catch (PDOException $ex) {
        $success = $ex->getMessage();
      }
    } else {
      $success = false;
    }
    return $success;
  }

  /**
   * CRUD+ : Save the current entity in the database
   * @return bool|string
   */
  public function save()
  {
    if ($this->new) {
      return $this->insert();
    } else {
      return $this->update();
    }
  }

  /**
   * CRUD+ : Delete the current entity from the database
   * @return bool|string
   */
  public function delete()
  {
    $sql = [
      "delete from",
      self::$table
    ];
    $data = [];
    foreach (self::$pkColumns as $column) {
      $attribute = self::$columnsMap[$column];
      $data[":$column"] = $this->$attribute;
    }

    $sql = self::where($sql);

    $statement = self::prepare($sql);
    if ($statement instanceof PDOStatement) {
      try {
        $success = $statement->execute($data);
      } catch (PDOException $ex) {
        $success = $ex->getMessage();
      }
    } else {
      $success = false;
    }
    return $success;
  }

}