<?php


namespace framework;

use JsonSerializable;

class ApiError implements JsonSerializable
{

  /**
   * @var string
   */
  private string $message;

  /**
   * ApiError constructor.
   * @param string $message
   */
  public function __construct(string $message)
  {
    $this->message = $message;
  }

  /**
   * @return array|mixed
   */
  public function jsonSerialize()
  {
    return get_object_vars($this);
  }
}