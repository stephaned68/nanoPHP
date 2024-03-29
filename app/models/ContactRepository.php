<?php


namespace app\models;


use Exception;
use framework\BaseRepository;
use framework\Database;
use framework\QueryBuilder;
use framework\Tools;

/**
 * DAO functions for the Contact entity
 * Class ContactRepository
 * @package app\models
 */
class ContactRepository extends BaseRepository
{
  /**
   * @return array
   * @throws Exception
   */
  public function getAllWithCategory(): array
  {
    $all = [];

    try {
      $qb = new QueryBuilder($this->table);
      $qb
        ->inner("categories","category_id", "category_id")
        ->select();
      $statement = Database::execute($qb->getQuery());
      $qb = null;
      while ($row = $statement->fetch()) {
        $contact = new Contact();
        Database::hydrate($contact, $row);
        $category = new Category();
        Database::hydrate($category, $row);
        $contact->setCategory($category);
        $all[] = $contact;
        $category = null;
        $contact = null;
      }
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }

    return $all;

  }

  /**
   * @param $id
   * @return Contact|null
   * @throws Exception
   */
  public function getOne($id): ?Contact
  {
    return parent::getOne($id);
  }
}