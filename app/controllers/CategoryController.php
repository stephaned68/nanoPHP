<?php


namespace app\controllers;

use app\models\Category;
use Exception;
use framework\BaseRepository;
use framework\FormManager;
use framework\Router;
use framework\Tools;
use framework\WebController;

/**
 * Category web routes
 * Class CategoryController
 * @package app\controllers
 */
class CategoryController extends WebController
{

  /**
   * @var BaseRepository Category DAO
   */
  private BaseRepository $categoryRepository;

  /**
   * CategoryController constructor.
   * @param BaseRepository|null $categoryRepository
   */
  public function __construct(?BaseRepository $categoryRepository)
  {
    if ($categoryRepository != null && is_subclass_of($categoryRepository, BaseRepository::class))
      $this->categoryRepository = $categoryRepository;

    $this->form = new FormManager();
    $this->form
      ->setTitle("Maintenance des catégories")
      ->addField([
        "name" => "categoryId",
        "filter" => FILTER_VALIDATE_INT,
        "controlType" => "hidden"
      ])
      ->addField([
        "name" => "categoryName",
        "label" => "Nom de la catégorie",
        "filter" => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        "required" => true
      ])
      ->setIndexRoute(Router::route([ "category", "index" ]))
    ;

  }

  /**
   * GET /category/index
   */
  public function indexAction() : void
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
  public function newAction() : void
  {
    $this->editAction(0);
  }

  /**
   * Category edit form
   * Display - GET /category/edit/{:id}
   * Process - POST
   * @param int|null $categoryId Category to edit
   * @throws Exception
   */
  public function editAction(?int $categoryId = null) : void
  {
    $form = $this->form;
    if ($categoryId !== 0)
      $form->setDeleteRoute(Router::route([ "category", "delete", $categoryId ]));

    $category = null;
    if ($categoryId != null) {
      $category = $this->categoryRepository->getOne($categoryId);
    }

    $this->render("category/edit", compact([
      "form",
      "category"
    ]));
  }

  /**
   * @throws Exception
   */
  public function editPostAction() : void
  {
    if (FormManager::isSubmitted()) {
      if (! FormManager::checkCSRFToken()) {
        FormManager::handleCSRF();
        Router::redirectTo(["category", "index"]);
        return;
      }
      $category = $this->form->getEntity(Category::class);
      $categoryId = $category->getCategoryId();
      if (!$this->form->isValid()) {
        Tools::setFlash($this->form->checkForm(), "warning");
        Router::redirectTo([ "category", "edit", $categoryId ]);
        return;
      }
      else {
        $rows = $this->categoryRepository->save($category);
        if ($rows > 0)
          Tools::setFlash("La catégorie a été enregistrée avec succès", "success");
        if (FormManager::isSubmitted([ "closeButton" ])) {
          Router::redirectTo([ "category" ]);
          return;
        }
        $category = null;
        Router::redirectTo([ "category", "new" ]);
      }
    }
  }

  /**
   * Delete a category
   * GET /category/delete/:id
   * @param int|null $categoryId Category id to delete
   * @throws Exception
   */
  public function deleteAction(?int $categoryId = null) : void
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