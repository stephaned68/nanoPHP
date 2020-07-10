<?php


namespace app\models;


use framework\BaseRepository;

class CategoryRepository extends BaseRepository
{
  public function getOne($id) : ?Category
  {
    return parent::getOne($id);
  }
}