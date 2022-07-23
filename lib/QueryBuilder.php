<?php

namespace framework;

use Exception;
use PDO;
use PDOStatement;

/**
 * Fluent methods to build SQL queries
 * Class QueryBuilder
 * @package framework
 */
class QueryBuilder
{
  /**
   * @var PDO|null
   */
  private ?PDO $pdo;

  /**
   * @var array
   */
  private array $table = [];

  /**
   * @var array
   */
  private array $joins = [];

  /**
   * @var array
   */
  private array $fields = [];

  /**
   * @var string
   */
  private string $distinct = "";

  /**
   * @var array
   */
  private array $where = [];

  /**
   * @var array
   */
  private array $orderBy = [];

  /**
   * @var array
   */
  private array $groupBy = [];

  /**
   * @var array
   */
  private array $params = [];

  /**
   * @var integer
   */
  private int $limit = 0;

  /**
   * @var integer
   */
  private int $offset = -1;

  /**
   * @var array
   */
  private array $insertValues = [];

  /**
   * @var array
   */
  private array $updateValues = [];

  /**
   * @var array
   */
  private array $deleteKeys = [];

  /**
   * QueryBuilder constructor.
   * @param string|null $table
   * @param string|null $alias
   * @throws Exception
   */
  public function __construct(string $table = null, string $alias = null)
  {
    $this->pdo = Database::getPDO();
    if ($table != null) {
      $this->from($table, $alias);
    }
  }

  /**
   * Add a table & alias to the query
   * @param string $table
   * @param string|null $alias
   * @return $this
   */
  public function from(string $table, string $alias = null): QueryBuilder
  {
    $this->table[] = $table . (($alias != null) ? " as $alias" : "");
    return $this;
  }

  /**
   * Set the projection
   * @param array $fields
   * @return $this
   */
  public function select(array $fields = []): QueryBuilder
  {
    if (count($fields) == 0) {
      $fields = ["*"];
    }
    $this->fields = $fields;
    return $this;
  }

  /**
   * SELECT with DISTINCT clause
   * @param array|string $field
   * @return $this
   */
  public function distinct(array|string $field): QueryBuilder
  {
    $this->distinct = "distinct ";
    if (!is_array($field))
      $field = [ $field ];
    $this->fields = $field;
    return $this;
  }

  /**
   * Add a WHERE clause
   * @param string $where
   * @return $this
   */
  public function where(string $where): QueryBuilder
  {
    $this->where[] = $where;
    return $this;
  }

  /**
   * Add an AND to the WHERE clause
   * @param string $where
   * @return $this
   */
  public function andWhere(string $where): QueryBuilder
  {
    return $this->where("AND {$where}");
  }

  /**
   * Add an OR to the WHERE clause
   * @param string $where
   * @return $this
   */
  public function orWhere(string $where): QueryBuilder
  {
    return $this->where("OR {$where}");
  }

  /**
   * Add an ORDER BY clause
   * @param string $orderBy
   * @return $this
   */
  public function orderBy(string $orderBy): QueryBuilder
  {
    if (strpos($orderBy, ".") == 0) {
      $orderBy = $this->alias ?? $this->table . "." . $orderBy;
    }
    $this->orderBy[] = $orderBy;
    return $this;
  }

  /**
   * Add an ORDER BY ... DESC clause
   * @param string $orderByDesc
   * @return $this
   */
  public function orderByDesc(string $orderByDesc): QueryBuilder
  {
    if (strpos($orderByDesc, ".") == 0) {
      $orderByDesc = $this->alias ?? $this->table . "." . $orderByDesc;
    }
    return $this->orderBy("DESC {$orderByDesc}");
  }

  /**
   * Add a JOIN clause
   * @param string $type
   * @param string $table
   * @param string $primaryKey
   * @param string $foreignKey
   * @param string|null $alias
   * @return $this
   */
  private function join(
    string $type,
    string $table,
    string $primaryKey,
    string $foreignKey,
    ?string $alias): QueryBuilder
  {
    $this->joins[] = [
      "type" => $type,
      "table" => $table,
      "pk" => $primaryKey,
      "fk" => $foreignKey,
      "alias" => $alias
    ];
    return $this;
  }

  /**
   * Add an INNER JOIN
   * @param string $table
   * @param string $primaryKey
   * @param string $foreignKey
   * @param string|null $alias
   * @return $this
   */
  public function inner(
    string $table,
    string $primaryKey,
    string $foreignKey,
    string $alias = null): QueryBuilder
  {
    return $this->join("INNER", $table, $primaryKey, $foreignKey, $alias);
  }

  /**
   * Add a LEFT JOIN
   * @param string $table
   * @param string $primaryKey
   * @param string $foreignKey
   * @param string $alias
   * @return $this
   */
  public function left(
    string $table,
    string $primaryKey,
    string $foreignKey,
    string $alias): QueryBuilder
  {
    return $this->join("LEFT", $table, $primaryKey, $foreignKey, $alias);
  }

  /**
   * Add a GROUP BY clause
   * @param string $groupBy
   * @return $this
   */
  public function groupBy(string $groupBy): QueryBuilder
  {
    if (strpos($groupBy, ".") == 0) {
      $groupBy = $this->alias ?? $this->table . "." . $groupBy;
    }
    $this->groupBy[] = $groupBy;
    return $this;
  }

  /**
   * Add a parameter name & value
   * @param string $key
   * @param string $value
   * @return $this
   */
  public function setParam(string $key, string $value): QueryBuilder
  {
    $this->params[$key] = $value;
    return $this;
  }

  /**
   * Add the LIMIT and OFFSET clause
   * @param int $limit
   * @param int $offset
   * @return $this
   */
  public function limit(int $limit, int $offset = -1): QueryBuilder
  {
    $this->limit = $limit;
    $this->offset = $offset;
    return $this;
  }

  /**
   * Add a WHERE ... IN (...) clause
   * @param string $column
   * @param $in
   * @return $this
   */
  public function in(string $column, $in): QueryBuilder
  {
    $inValues = $in;
    if (is_array($in)) {
      $inValues = "(" . implode(", ", $in) . ")";
    }
    else if (is_object($in)) {
      if (get_class($in) == "QueryBuilder") {
        $inValues = $in->getSubQuery();
      }
    }
    $this->where[] = "$column IN $inValues";
    return $this;
  }

  /**
   * Add a OR ... IN (...) WHERE clause
   * @param string $column
   * @param $in
   * @return $this
   */
  public function orIn(string $column, $in): QueryBuilder
  {
    return $this->in(" OR $column", $in);
  }

  /**
   * Add a AND ... IN (...) WHERE clause
   * @param string $column
   * @param $in
   * @return $this
   */
  public function andIn(string $column, $in): QueryBuilder
  {
    return $this->in(" AND $column", $in);
  }

  /**
   * Return the built SQL query
   * @return string
   */
  public function getQuery(): string
  {
    $sql = [];

    $sql[] = "SELECT " . $this->distinct . implode(", ", $this->fields);
    $sql[] = "FROM " . implode(", ", $this->table);
    if (count($this->joins) > 0) {
      foreach ($this->joins as $join) {
        $joinTable = "{$join["type"]} JOIN {$join["table"]}";
        if ($join["fk"] = $join["pk"]) {
          if (!is_array($join["fk"]))
            $join["fk"] = [ $join["fk"] ];
          $joinTable .= " USING (" . implode(", ", $join["fk"]) . ")";
        }
        else {
          $joinTable .= (($join["alias"] != null) ? " AS {$join["alias"]}" : "")
          . " ON {$join["fk"]} = {$join["pk"]}";
        }
        $sql[] = $joinTable;
      }
    }
    if (count($this->where) > 0) {
      $sql[] = "WHERE " . implode(" ", $this->where);
    }
    if (count($this->groupBy) > 0) {
      $sql[] = "GROUP BY " . implode(", ", $this->groupBy);
    }
    if (count($this->orderBy) > 0) {
      $sql[] = "ORDER BY " . implode(", ", $this->orderBy);
    }
    if ($this->limit > 0) {
      $sql[] = "LIMIT {$this->limit}";
      if ($this->offset > -1) {
        $sql[] = "OFFSET {$this->offset}";
      }
    }

    return implode(" ", $sql);
  }

  /**
   * Return as a sub-query
   * @return string
   */
  public function getSubQuery(): string
  {
    return "(" . $this->getQuery() . ")";
  }

  /**
   * Prepare and return a PDO statement
   * @param string|null $sqlQuery
   * @param array $params
   * @return bool|PDOStatement
   * @throws Exception
   */
  public function execute(string $sqlQuery = null, array $params = []): bool|PDOStatement
  {
    if ($sqlQuery == null)
      $sqlQuery = $this->getQuery();

    if (count($params) == 0) {
      $params = $this->params;
    }
    return Database::execute($sqlQuery, $params);
  }

  /**
   * Pass data to insert
   * @param array $values
   * @return $this
   */
  public function insert(array $values) : QueryBuilder
  {

    $this->insertValues = $values;
    return $this;
  }

  /**
   * Pass data to update
   * @param array $values
   * @return $this
   */
  public function update(array $values) : QueryBuilder
  {
    $this->updateValues = $values;
    return $this;
  }

  /**
   * Pass primary key(s) to delete
   * @param array $keys
   * @return $this
   */
  public function delete(array $keys) : QueryBuilder
  {
    $this->deleteKeys = $keys;
    return $this;
  }

  /**
   * Build an INSERT query
   * @return string
   */
  private function insertQuery() : string
  {
    $sql = [];

    $sql[] = "INSERT INTO " . $this->table[0];
    $columns = [];
    $values = [];
    foreach ($this->insertValues as $column => $value) {
      if (!is_array($value) && !is_object($value) && $value != null) {
        $columns[] = $column;
        $values[] = ":" . $column;
      }
    }
    $sql[] = "(" . implode(", ", $columns). ")";
    $sql[] = "VALUES (" . implode(", ", $values) . ")";

    return implode(" ", $sql);
  }

  /**
   * Build an UPDATE query
   * @return string
   */
  private function updateQuery() : string
  {
    $sql = [];

    $sql[] = "UPDATE " . $this->table[0] . " SET";
    $values = [];
    foreach ($this->updateValues as $column => $value) {
      if (!is_array($value) && !is_object($value)) {
        $values[] = $column . " = :" . $column;
      }
    }
    $sql[] = implode(", ", $values);
    if (count($this->where) > 0) {
      $sql[] = "WHERE " . implode(" ", $this->where);
    }

    return implode(" ", $sql);
  }

  /**
   * Build a DELETE query
   * @return string
   */
  private function deleteQuery() : string
  {
    $sql = [];

    $sql[] = "DELETE FROM " . $this->table[0];
    if (count($this->where) > 0) {
      $sql[] = "WHERE " . implode(" ", $this->where);
    }

    return implode(" ", $sql);
  }

  /**
   * Generate and execute an INSERT / UPDATE / DELETE query
   * @return bool|PDOStatement|null
   * @throws Exception
   */
  public function commit(): bool|PDOStatement|null
  {
    $inserts = count($this->insertValues);
    $updates = count($this->updateValues);
    $deletes = count($this->deleteKeys);

    if ($inserts == 0 && $updates == 0 && $deletes == 0)
      throw new Exception("Nothing to commit");

    if ($inserts > 0 && $updates == 0 && $deletes == 0) {
      return $this->execute($this->insertQuery(), $this->insertValues);
    }

    if ($inserts == 0 && $updates > 0 && $deletes == 0) {
      return $this->execute($this->updateQuery(), $this->updateValues);
    }

    if ($inserts == 0 && $updates == 0 && $deletes > 0) {
      var_dump($this->deleteQuery());
      return $this->execute($this->deleteQuery(), $this->deleteKeys);
    }

    return null;
  }

}