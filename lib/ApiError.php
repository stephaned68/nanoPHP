<?php

namespace framework;

use JsonSerializable;

/**
 * Represents an API error
 * Class ApiError
 * @package framework
 */
class ApiError implements JsonSerializable
{

  /**
   * @var string Error message
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
   * @return array
   */
  public function jsonSerialize(): array
  {
    return get_object_vars($this);
  }
}