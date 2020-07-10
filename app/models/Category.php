<?php


namespace app\models;

class Category
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


}