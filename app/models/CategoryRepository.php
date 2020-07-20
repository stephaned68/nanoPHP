<?php


namespace app\models;


use framework\BaseRepository;

/**
 * DAO functions for the Category entity
 * Class CategoryRepository
 * @package app\models
 */
class CategoryRepository extends BaseRepository
{
  public function getOne($id) : ?Category
  {
    return parent::getOne($id);
  }
}