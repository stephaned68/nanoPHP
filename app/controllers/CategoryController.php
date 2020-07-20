<?php


namespace app\controllers;

use app\models\Category;
use Exception;
use framework\BaseRepository;
use framework\FormManager;
use framework\Router;
use framework\Tools;

/**
 * Category web routes
 * Class CategoryController
 * @package app\controllers
 */
class CategoryController extends BaseController
{

  /**
   * @var BaseRepository Category DAO
   */
  private BaseRepository $categoryRepository;

  /**
   * CategoryController constructor.
   * @param BaseRepository $categoryRepository
   */
  public function __construct(?BaseRepository $categoryRepository)
  {
    if ($categoryRepository != null && is_subclass_of($categoryRepository, BaseRepository::class))
      $this->categoryRepository = $categoryRepository;
  }

  /**
   * GET /category/index
   */
  public function indexAction()
  {
    $categories = [];
    try {
      $categories = $this->categoryRepository->getAll();
    } catch (Exception $e) {
      Tools::setFlash($e->getMessage(), "danger");
    }

    $this->render("category/index", [
      "title" => "Liste des catégories",
      "categories" => $categories
    ]);
  }

  /**
   * New category form
   * Display - GET /category/new
   * Process - POST
   * @throws Exception
   */
  public function newAction()
  {
    $this->editAction();
  }

  /**
   * Category edit form
   * Display - GET /category/edit/{:id}
   * Process - POST
   * @param int $categoryId Category to edit
   * @throws Exception
   */
  public function editAction($categoryId = null)
  {
    $form = new FormManager();
    $form
      ->setTitle("Maintenance des catégories")
      ->addField([
        "name" => "categoryId",
        "filter" => FILTER_VALIDATE_INT,
        "controlType" => "hidden"
      ])
      ->addField([
        "name" => "categoryName",
        "label" => "Nom de la catégorie",
        "filter" => FILTER_SANITIZE_STRING,
        "required" => true
      ])
      ->setIndexRoute(Router::route([ "category", "index" ]))
      ->setDeleteRoute(Router::route([ "category", "delete", $categoryId ]))
    ;

    $category = null;
    if ($categoryId != null) {
      $category = $this->categoryRepository->getOne($categoryId);
    }

    if (FormManager::isSubmitted()) {
      if (!$form->isValid()) {
        $errors = $form->validateForm();
        foreach ($errors as $error) {
          Tools::setFlash($error, "warning");
        }
        Router::redirectTo([ "category", "edit", $categoryId ]);
        return;
      }
      else {
        $category = $form->getEntity(Category::class);
        $rows = $this->categoryRepository->save($category);
        if ($rows > 0)
          Tools::setFlash("La catégorie a été enregistrée avec succès", "success");
        if (FormManager::isSubmitted([ "closeButton" ])) {
          Router::redirectTo([ "category" ]);
          return;
        }
        $category = null;
      }
    }

    $this->getView()->setVariable("form", $form);
    $this->getView()->setVariable("category", $category);
    $this->render("category/edit");
  }

  /**
   * Delete a category
   * GET /category/delete/:id
   * @param int $categoryId Category id to delete
   * @throws Exception
   */
  public function deleteAction($categoryId = null)
  {
    if ($categoryId != null) {
      $category = $this->categoryRepository->getOne($categoryId);
      if ($category == null) {
        Tools::setFlash("Cet identifiant de catégorie n'existe pas", "warning");
      }
      else {
        $this->categoryRepository->deleteOne($category);
        Tools::setFlash("La catégorie a été supprimée avec succès", "success");
      }
    }
    Router::redirectTo([ "category" ]);
  }
}