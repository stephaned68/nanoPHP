<?php


namespace app\models;


use Exception;
use framework\BaseRepository;

/**
 * DAO functions for the Category entity
 * Class CategoryRepository
 * @package app\models
 */
class CategoryRepository extends BaseRepository
{
  /**
   * @param $id
   * @return Category|null
   * @throws Exception
   */
  public function getOne($id) : ?Category
  {
    return parent::getOne($id);
  }
}