<?php

namespace app\models;

use JsonSerializable;

/**
 * Represents a Category entity
 * Class Category
 * @package app\models
 */
class Category implements JsonSerializable
{
  /**
   * @var int
   */
  protected ?int $categoryId = null;

  /**
   * @var string
   */
  protected string $categoryName;

  /**
   * @return int|null
   */
  public function getCategoryId(): ?int
  {
    return $this->categoryId;
  }

  /**
   * @param int $categoryId
   * @return Category
   */
  public function setCategoryId(int $categoryId): Category
  {
    $this->categoryId = $categoryId;
    return $this;
  }

  /**
   * @return string
   */
  public function getCategoryName(): string
  {
    return $this->categoryName;
  }

  /**
   * @param string $categoryName
   * @return Category
   */
  public function setCategoryName(string $categoryName): Category
  {
    $this->categoryName = $categoryName;
    return $this;
  }

  /**
   * @return array|mixed
   */
  public function jsonSerialize()
  {
    return get_object_vars($this);
  }
}