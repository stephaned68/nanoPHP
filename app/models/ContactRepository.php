<?php


namespace app\models;


use framework\BaseRepository;

class ContactRepository extends BaseRepository
{
  public function getOne($id): ?Contact
  {
    return parent::getOne($id);
  }

}