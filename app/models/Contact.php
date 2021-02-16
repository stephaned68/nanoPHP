<?php


namespace app\models;

use Exception;

/**
 * Represents a Contact entity
 * Class Contact
 * @package app\models
 */
class Contact
{
  /**
   * @var int|null
   */
  private ?int $contactId;

  /**
   * @var string
   */
  private string $contactName;

  /**
   * @var string
   */
  private string $contactEmail;

  /**
   * @var int
   */
  private int $categoryId;

  /**
   * @var Category|null
   */
  private ?Category $category = null;

  /**
   * @return int|null
   */
  public function getContactId(): ?int
  {
    return $this->contactId;
  }

  /**
   * @param int|null $contactId
   * @return Contact
   */
  public function setContactId(?int $contactId): Contact
  {
    $this->contactId = $contactId;
    return $this;
  }

  /**
   * @return string
   */
  public function getContactName(): string
  {
    return $this->contactName;
  }

  /**
   * @param string $contactName
   * @return Contact
   */
  public function setContactName(string $contactName): Contact
  {
    $this->contactName = $contactName;
    return $this;
  }

  /**
   * @return string
   */
  public function getContactEmail(): string
  {
    return $this->contactEmail;
  }

  /**
   * @param string $contactEmail
   * @return Contact
   */
  public function setContactEmail(string $contactEmail): Contact
  {
    $this->contactEmail = $contactEmail;
    return $this;
  }

  /**
   * @return int
   */
  public function getCategoryId(): int
  {
    return $this->categoryId;
  }

  /**
   * @param int $categoryId
   * @return Contact
   */
  public function setCategoryId(int $categoryId): Contact
  {
    $this->categoryId = $categoryId;
    return $this;
  }

  /**
   * @return Category
   * @throws Exception
   */
  public function getCategory(): Category
  {
    if ($this->category == null) { // LAZY loading
      $categoryRepository = new CategoryRepository();
      $this->category = $categoryRepository->getOne($this->categoryId);
      $categoryRepository = null;
    }
    return $this->category;
  }

  /**
   * @param Category $category
   * @return Contact
   */
  public function setCategory(Category $category): Contact
  {
    $this->category = $category;
    $this->categoryId = (int)$category->getCategoryId();
    return $this;
  }

}