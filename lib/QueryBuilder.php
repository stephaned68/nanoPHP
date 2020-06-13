<?php

namespace framework;

use PDO;
use PDOStatement;

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
   * QueryBuilder constructor.
   * @param string $table
   * @param string|null $alias
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
  public function from(string $table, string $alias = null)
  {
    $this->table[] = $table . (($alias != null) ? " as $alias" : "");
    return $this;
  }

  /**
   * Set the projection
   * @param array $fields
   * @return $this
   */
  public function select(array $fields = [])
  {
    if (count($fields) == 0) {
      $fields = ["*"];
    }
    $this->fields = $fields;
    return $this;
  }

  /**
   * Add a WHERE clause
   * @param string $where
   * @return $this
   */
  public function where(string $where)
  {
    $this->where[] = $where;
    return $this;
  }

  /**
   * Add an AND to the WHERE clause
   * @param string $where
   * @return $this
   */
  public function andWhere(string $where)
  {
    return $this->where("and {$where}");
  }

  /**
   * Add an OR to the WHERE clause
   * @param string $where
   * @return $this
   */
  public function orWhere(string $where)
  {
    return $this->where("or {$where}");
  }

  /**
   * Add an ORDER BY clause
   * @param string $orderBy
   * @return $this
   */
  public function orderBy(string $orderBy)
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
  public function orderByDesc(string $orderByDesc)
  {
    if (strpos($orderByDesc, ".") == 0) {
      $orderByDesc = $this->alias ?? $this->table . "." . $orderByDesc;
    }
    return $this->orderBy("desc {$orderByDesc}");
  }

  /**
   * Add a JOIN clause
   * @param string $type
   * @param string $table
   * @param string $primaryKey
   * @param string $foreignKey
   * @param string $alias
   * @return $this
   */
  private function join(
    string $type,
    string $table,
    string $primaryKey,
    string $foreignKey,
    string $alias)
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
    string $alias = null)
  {
    return $this->join("inner", $table, $primaryKey, $foreignKey, $alias);
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
    string $alias)
  {
    return $this->join("left", $table, $primaryKey, $foreignKey, $alias);
  }

  /**
   * Add a GROUP BY clause
   * @param string $groupBy
   * @return $this
   */
  public function groupBy(string $groupBy)
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
  public function setParam(string $key, string $value)
  {
    $this->params[$key] = $value;
    return $this;
  }

  /**
   * Add the LIMIT and OFFSET clause
   * @param int $limit
   * @param int|null $offset
   * @return $this
   */
  public function limit(int $limit, ?int $offset = null)
  {
    $this->limit = $limit;
    $this->offset = $offset;
    return $this;
  }

  /**
   * Return the built SQL query
   * @return string
   */
  public function getQuery()
  {
    $sql = [];

    $sql[] = "select " . implode(", ", $this->fields);
    $sql[] = "from " . implode(", ", $this->table);
    if (count($this->joins) > 0) {
      foreach ($this->joins as $join) {
        $sql[] = "{$join["type"]} join {$join["table"]}"
          . (($join["alias"] != null) ? " as {$join["alias"]}" : "")
          . " on {$join["fk"]} = {$join["pk"]}";
      }
    }
    if (count($this->where) > 0) {
      $sql[] = "where " . implode(" ", $this->where);
    }
    if (count($this->groupBy) > 0) {
      $sql[] = "group by " . implode(", ", $this->groupBy);
    }
    if (count($this->orderBy) > 0) {
      $sql[] = "order by " . implode(", ", $this->orderBy);
    }
    if ($this->limit > 0) {
      $sql[] = "limit {$this->limit}";
      if ($this->offset > -1) {
        $sql[] = "offset {$this->offset}";
      }
    }

    return implode(" ", $sql);
  }

  /**
   * Prepare and return a PDO statement
   * @param array $params
   * @return bool|PDOStatement
   */
  public function prepare(array $params = [])
  {
    if (count($params) == 0) {
      $params = $this->params;
    }
    $statement = $this->pdo->prepare($this->getQuery());
    if (count($params) > 0) {
      foreach ($params as $key => $value) {
        $statement->bindValue($key, $value);
      }
    }
    $statement->execute();
    return $statement;
  }
}