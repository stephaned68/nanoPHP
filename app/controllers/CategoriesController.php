<?php


namespace app\controllers;

use app\models\Category;
use Exception;
use framework\ApiController;
use framework\ApiError;
use framework\BaseRepository;
use framework\HttpHelper;
use framework\Tools;

/**
 * Category API endpoints
 * Class CategoriesController
 * @package app\controllers
 */
class CategoriesController extends ApiController
{
  /**
   * @var BaseRepository|null Category DAO
   */
  private ?BaseRepository $categoryRepository;

  /**
   * CategoriesController constructor.
   * @param BaseRepository|null $categoryRepository
   */
  public function __construct(?BaseRepository $categoryRepository)
  {
    $this->categoryRepository = $categoryRepository;
    $this->responseCode = HttpHelper::$STATUS_OK;
  }

  /**
   * GET /api/category - Return all categories
   * GET /api/category/:id - Return one category
   * @param mixed|null $id Category id to retrieve
   * @return void
   * @throws Exception
   */
  public function processGet(mixed $id = null): void
  {
    if ($id == null) {
      $response = $this->categoryRepository->getAll();
    }
    else {
      try {
        $response = $this->categoryRepository->getOne($id);
        if ($response == null) {
          $this->setResponseCode(HttpHelper::$STATUS_NOTFOUND);
          $response = new ApiError("Category not found");
        }
      } catch (Exception $e) {
        $this->setResponseCode(HttpHelper::$STATUS_ERROR);
        $response = new ApiError($e->getMessage());
      }
    }
    $this->sendResponse($response);
  }

  /**
   * POST /api/category - add a new category
   * @return void
   */
  public function processPost(): void
  {
    $category = $this->loadEntity(Category::class);

    $rows = 0;
    try {
      $rows = $this->categoryRepository->save($category);
      $response = $category;
    } catch (Exception $e) {
      $response = new ApiError($e->getMessage());
    }

    $status = ($rows == 0) ? HttpHelper::$STATUS_ERROR : HttpHelper::$STATUS_CREATED;
    $this->setResponseCode($status);

    $this->sendResponse($response);
  }

  /**
   * PUT /api/category/:id - update a category
   * @param mixed $id Category id to update
   * @return void
   * @throws Exception
   */
  public function processPut(mixed $id): void
  {
    $category = $this->categoryRepository->getOne($id);

    if ($category != null) {
      $rows = 0;
      $updatedCategory = $this->loadEntity(Category::class);
      Tools::setProperty($updatedCategory, "categoryId", $id);

      try {
        $rows = $this->categoryRepository->save($updatedCategory);
        $response = $updatedCategory;
      } catch (Exception $e) {
        $response = new ApiError($e->getMessage());
      }

      if ($rows == 0)
        $this->setResponseCode(HttpHelper::$STATUS_ERROR);
    }
    else {
      $this->setResponseCode(HttpHelper::$STATUS_NOTFOUND);
      $response = new ApiError("Category not found");
    }

    $this->sendResponse($response);
  }

  /**
   * DELETE /api/category/:id
   * @param mixed $id Category id to delete
   * @return void
   * @throws Exception
   */
  public function processDelete(mixed $id): void
  {
    $category = $this->categoryRepository->getOne($id);

    if ($category != null) {
      $rows = 0;

      try {
        $rows = $this->categoryRepository->deleteOne($category);
        $response = "";
      } catch (Exception $e) {
        $response = new ApiError($e->getMessage());
      }

      $status = ($rows == 0) ? HttpHelper::$STATUS_ERROR : HttpHelper::$STATUS_EMPTY;
      $this->setResponseCode($status);
    }
    else {
      $this->setResponseCode(HttpHelper::$STATUS_NOTFOUND);
      $response = new ApiError("Category not found");
    }

    $this->sendResponse($response);
  }
}