<?php


namespace app\models;

use framework\BaseModel;

class Contact extends BaseModel
{

  /**
   * @var string
   */
  private string $contactId = "";

  /**
   * @var string
   */
  private string $name = "";

  /**
   * @return string
   */
  public function getContactId(): string
  {
    return $this->contactId;
  }

  /**
   * @param string $contactId
   */
  public function setContactId(string $contactId): void
  {
    $this->contactId = $contactId;
  }

  /**
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public static function configure()
  {
    parent::initialize("Contact", [
      "table" => "",
      "pkColumns" => [

      ],
      "columnsMap" => [

      ]
    ]);
  }
}