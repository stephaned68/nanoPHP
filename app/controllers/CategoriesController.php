<?php


namespace app\controllers;

use app\models\Category;
use Exception;
use framework\ApiError;
use framework\BaseRepository;
use framework\Tools;

/**
 * Category API endpoints
 * Class CategoriesController
 * @package app\controllers
 */
class CategoriesController extends ApiController
{
  /**
   * @var BaseRepository Category DAO
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

  /**
   * GET /api/category - Return all categories
   * GET /api/category/:id - Return one category
   * @param int $categoryId Category id to retrieve
   * @return mixed|void
   * @throws Exception
   */
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

  /**
   * POST /api/category - add a new category
   * @return mixed|void
   */
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

    $status = ($rows == 0) ? 500 : 201;
    $this->setResponseCode($status);

    $this->sendResponse($response);
  }

  /**
   * PUT /api/category/:id - update a category
   * @param int $categoryId Category id to update
   * @return mixed|void
   * @throws Exception
   */
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

  /**
   * DELETE /api/category/:id
   * @param int $categoryId Category id to delete
   * @return mixed|void
   * @throws Exception
   */
  public function doDelete($categoryId)
  {
    $category = $this->categoryRepository->getOne($categoryId);

    if ($category != null) {
      $rows = 0;

      try {
        $rows = $this->categoryRepository->deleteOne($category);
        $response = "";
      } catch (Exception $e) {
        $response = new ApiError($e->getMessage());
      }

      $status = ($rows == 0) ? 500 : 204;
      $this->setResponseCode($status);
    }
    else {
      $this->setResponseCode(404);
      $response = new ApiError("Category not found");
    }

    $this->sendResponse($response);
  }
}