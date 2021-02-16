<?php

namespace framework;

use Exception;

/**
 * Base DAO functions for entities
 * Class BaseRepository
 * @package framework
 */
abstract class BaseRepository implements RepositoryInterface
{
  /**
   * @var string Entity name
   */
  protected string $entity;

  /**
   * @var string Full class name
   */
  protected string $class;

  /**
   * @var string Table name
   */
  protected string $table;

  /**
   * @var array Primary key columns
   */
  protected array $pkColumns;

  /**
   * @var bool Generated primary key
   */
  protected bool $generatedPK = true;

  /**
   * BaseRepository constructor.
   */
  public function __construct()
  {
    $class = explode(DIRECTORY_SEPARATOR, get_class($this));
    $this->entity = str_replace("Repository", "", $class[count($class) - 1]);
    $this->class = implode(DIRECTORY_SEPARATOR, [ "app", "models", $this->entity ]);
    $this->table = Database::tableName($this->entity);
    $this->pkColumns = [ Database::columnName($this->entity . "Id") ];
  }

  /**
   * Get all entities
   * @return array
   * @throws Exception
   */
  public function getAll() : array
  {
    try {
      $qb = new QueryBuilder($this->table);
      $qb->select();
      $statement = Database::execute($qb->getQuery());
      $qb = null;
      $all = Database::fetchEntities($statement, $this->class);
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }

    return $all;
  }

  /**
   * Get one entity
   * @param $id
   * @return object
   * @throws Exception
   */
  public function getOne($id) : ?object
  {
    if (!is_array($id))
      $id = [ $this->pkColumns[0] => $id ];
    $qb = new QueryBuilder($this->table);
    $qb->select();
    foreach($this->pkColumns as $ix => $pkColumn) {
      if ($ix == 0) {
        $qb->where($pkColumn . " = :" . $pkColumn);
      }
      else {
        $qb->andWhere($pkColumn . " = :" . $pkColumn);
      }
    }
    $statement = Database::execute($qb->getQuery(), $id);
    $qb = null;
    return Database::fetchEntity($statement, $this->class);
  }

  /**
   * Filter entities by criteria
   * @param array $filters
   * @return array
   * @throws Exception
   */
  public function findBy(array $filters) : array
  {
    $ix = 0;
    $qb = new QueryBuilder($this->table);
    $qb->select();
    foreach($filters as $column => $value) {
      if ($ix++ == 0) {
        $qb->where($column . " = :" . $column);
      }
      else {
        $qb->andWhere($column . " = :" . $column);
      }
    }
    $statement = Database::execute($qb->getQuery(), $filters);
    $qb = null;

    return Database::fetchEntities($statement, $this->class);
  }

  /**
   * Insert an entity
   * @param object $entity
   * @return int
   * @throws Exception
   */
  public function insertOne(object $entity) : int
  {
    $rows = 0;

    $data = Database::entityToArray($entity);
    $qb = new QueryBuilder($this->table);
    try {
      $statement = $qb->insert($data)->commit();
      $rows += $statement->rowCount();
      if ($this->generatedPK) {
        Tools::setProperty($entity, Tools::pascalize($this->pkColumns[0]), Database::getPDO()->lastInsertId());
      }
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
    $qb = null;

    return $rows;
  }

  /**
   * Update an entity
   * @param object $entity
   * @return int
   * @throws Exception
   */
  public function updateOne(object $entity) : int
  {
    $rows = 0;

    $data = Database::entityToArray($entity);
    try {
      $qb = new QueryBuilder($this->table);
      foreach($this->pkColumns as $ix => $pkColumn) {
        if ($ix == 0) {
          $qb->where($pkColumn . " = :" . $pkColumn);
        }
        else {
          $qb->andWhere($pkColumn . " = :" . $pkColumn);
        }
      }
      $statement = $qb->update($data)->commit();
      $qb = null;
      $rows += $statement->rowCount();
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }

    return $rows;
  }

  /**
   * Save an entity
   * @param object $entity
   * @return int
   * @throws Exception
   */
  public function save(object $entity) : int
  {
    $inserted = false;

    if ($this->generatedPK) {
      $data = Database::entityToArray($entity);
      $nulls = 0;
      foreach ($this->pkColumns as $pkColumn) {
        if ($data[$pkColumn] == null)
          $nulls++;
      }
      $inserted = ($nulls == count($this->pkColumns));
    }
    else {
      $inserted = Tools::getProperty($entity, "isNew");
    }

    if ($inserted) {
      $rows = $this->insertOne($entity);
    } else {
      $rows = $this->updateOne($entity);
    }

    return $rows;
  }

  /**
   * Delete an entity
   * @param object $entity
   * @return int
   * @throws Exception
   */
  public function deleteOne(object $entity) : int
  {
    $rows = 0;

    $keys = [];
    foreach ($this->pkColumns as $pkColumn) {
      $keys[$pkColumn] = Tools::getProperty($entity, Tools::pascalize($pkColumn));
    }

    try {
      $qb = new QueryBuilder($this->table);
      foreach($this->pkColumns as $ix => $pkColumn) {
        if ($ix == 0) {
          $qb->where($pkColumn . " = :" . $pkColumn);
        }
        else {
          $qb->andWhere($pkColumn . " = :" . $pkColumn);
        }
      }
      $statement = $qb->delete($keys)->commit();
      $qb = null;
      $rows += $statement->rowCount();
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }

    return $rows;
  }
}