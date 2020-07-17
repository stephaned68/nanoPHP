<?php


namespace app\controllers;

use app\models\Category;
use Exception;
use framework\ApiError;
use framework\BaseRepository;
use framework\Tools;

class CategoriesController extends ApiController
{
  /**
   * @var BaseRepository
   */
  private ?BaseRepository $categoryRepository;

  /**
   * CategoriesController constructor.
   * @param BaseRepository $categoryRepository
   */
  public function __construct(?BaseRepository $categoryRepository)
  {
    $this->categoryRepository = $categoryRepository;
  }

  public function doGet($categoryId = null)
  {
    if ($categoryId == null) {
      $response = $this->categoryRepository->getAll();
    }
    else {
      try {
        $response = $this->categoryRepository->getOne($categoryId);
        if ($response == null) {
          $this->setResponseCode(404);
          $response = new ApiError("Category not found");
        }
      } catch (Exception $e) {
        $this->setResponseCode(500);
        $response = new ApiError($e->getMessage());
      }
    }
    $this->sendResponse($response);
  }

  public function doPost()
  {
    $category = $this->loadEntity(Category::class);

    $rows = 0;
    try {
      $rows = $this->categoryRepository->save($category);
      $response = $category;
    } catch (Exception $e) {
      $response = new ApiError($e->getMessage());
    }
    if ($rows == 0)
      $this->setResponseCode(500);

    $this->sendResponse($response);
  }

  public function doPut($categoryId)
  {
    $category = $this->categoryRepository->getOne($categoryId);

    if ($category != null) {
      $rows = 0;
      $updatedCategory = $this->loadEntity(Category::class);
      Tools::setProperty($updatedCategory, "categoryId", $categoryId);

      try {
        $rows = $this->categoryRepository->save($updatedCategory);
        $response = $updatedCategory;
      } catch (Exception $e) {
        $response = new ApiError($e->getMessage());
      }
      if ($rows == 0)
        $this->setResponseCode(500);
    }
    else {
      $this->setResponseCode(404);
      $response = new ApiError("Category not found");
    }

    $this->sendResponse($response);
  }

  public function doDelete($id)
  {
    // TODO: Implement doDelete() method.
  }
}