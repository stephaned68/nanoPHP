<?php


namespace app\models;


use framework\BaseRepository;

/**
 * DAO functions for the Contact entity
 * Class ContactRepository
 * @package app\models
 */
class ContactRepository extends BaseRepository
{
  public function getOne($id): ?Contact
  {
    return parent::getOne($id);
  }

}