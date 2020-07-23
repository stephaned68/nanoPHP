<?php


namespace app\controllers;

use app\models\CategoryRepository;
use app\models\Contact;
use app\models\ContactRepository;
use Exception;
use framework\FormManager;
use framework\Router;
use framework\Tools;

class ContactController extends BaseController
{

  /**
   * @var ContactRepository Contact DAO
   */
  private ContactRepository $contactRepository;

  /**
   * ContactController constructor.
   * @param ContactRepository $contactRepository
   */
  public function __construct(ContactRepository $contactRepository)
  {
    $this->contactRepository = $contactRepository;
  }

  /**
   * Contacts list
   * GET /contact/index
   * @throws Exception
   */
  public function indexAction() : void
  {
    $contacts = [];
    try {
      $contacts = $this->contactRepository->getAllWithCategory();
    } catch (Exception $e) {
      Tools::setFlash($e->getMessage(), "danger");
    }

    $this->render("contact/index", [
      "title" => "Liste des contacts",
      "contacts" => $contacts
    ]);
  }

  /**
   * New contact form
   * Display - GET /contact/new
   * Process - POST
   * @throws Exception
   */
  public function newAction() : void
  {
    $this->editAction();
  }

  /**
   * Contact edit form
   * Display - GET /contact/edit/{:id}
   * Process - POST
   * @param int|null $contactId Contact id to edit
   * @throws Exception
   */
  public function editAction(?int $contactId = null) : void
  {
    $categoryRepository = new CategoryRepository();
    $categoryList = Tools::select($categoryRepository->getAll(), "categoryId", "categoryName");

    $form = new FormManager();
    $form
      ->setTitle("Maintenance des Contacts")
      ->addField([
        "name" => "contactId",
        "filter" => FILTER_VALIDATE_INT,
        "controlType" => "hidden"
      ])
      ->addField([
        "name" => "contactName",
        "label" => "Nom du contact",
        "required" => true
      ])
      ->addField([
        "name" => "contactEmail",
        "label" => "Email du contact",
        "controlType" => "email"
      ])
      ->addField([
        "name" => "categoryId",
        "label" => "Catégorie de contact",
        "required" => true,
        "controlType" => "select",
        "valueList" => $categoryList
      ])
      ->setIndexRoute(Router::route([ "contact", "index" ]))
      ->setDeleteRoute(Router::route([ "contact", "delete", $contactId ]))
    ;

    $contact = null;
    if ($contactId != null) {
      $contact = $this->contactRepository->getOne($contactId);
    }

    if (FormManager::isSubmitted()) {
      if (!$form->isValid()) {
        Tools::setFlash($form->checkForm(), "warning");
        Router::redirectTo([ "category", "edit", $contactId ]);
        return;
      }
      else {
        $contact = $form->getEntity(Contact::class);
        $rows = $this->contactRepository->save($contact);
        if ($rows > 0)
          Tools::setFlash("Le contact a été enregistré avec succès", "success");
        if (FormManager::isSubmitted([ "closeButton" ])) {
          Router::redirectTo([ "contact" ]);
          return;
        }
        $contact = null;
      }
    }

    $this->render("contact/edit", compact([
      "form",
      "contact"
    ]));
  }

  /**
   * Delete a contact
   * GET /contact/delete/:id
   * @param null $contactId Contact id to delete
   * @throws Exception
   */
  public function deleteAction($contactId = null) : void
  {
    if ($contactId != null) {
      $contact = $this->contactRepository->getOne($contactId);
      if ($contact == null) {
        Tools::setFlash("Cet identifiant de contact n'existe pas", "warning");
      }
      else {
        $this->contactRepository->deleteOne($contact);
        Tools::setFlash("Le contact a été supprimé avec succès", "success");
      }
    }
    Router::redirectTo([ "contact" ]);
  }
}