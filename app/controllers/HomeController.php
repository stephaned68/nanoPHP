<?php

namespace app\controllers;

use app\models\Contact;
use framework\FormManager;

class HomeController extends BaseController
{

  public function indexAction()
  {
    $this->render("home/index", []);
  }

  private function getEditForm()
  {
    $editForm = new FormManager();
    $editForm
      ->setTitle("Maintenance des Contacts")
      ->addField([
        "name" => "contactId",
        "label" => "Id. du contact",
        "primeKey" => true
      ])
      ->addField([
        "name" => "name",
        "label" => "Nom du contact",
        "required" => true
      ]);
    return $editForm;
  }

  public function getEdit()
  {
    $fm = $this->getEditForm();

    $this->getView()->setVariable("form", $fm);
    $this->render("home/edit");
  }

  public function postEdit()
  {
    $editForm = $this->getEditForm();

    Contact::configure();
    $contact = new Contact($editForm->getData());
    var_dump($contact);

    $test = Contact::findByName("Stéphane");
    var_dump($test);
  }

}