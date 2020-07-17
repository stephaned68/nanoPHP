<?php

namespace framework;

class FormManager
{

  /**
   * @var string
   */
  private string $title;

  /**
   * @var array
   */
  private array $formFields;

  /**
   * @var string
   */
  private string $indexRoute;

  /**
   * @var string
   */
  private string $deleteRoute;

  /**
   * @return string
   */
  public function getTitle(): string
  {
    return $this->title;
  }

  /**
   * @param string $title
   * @return FormManager
   */
  public function setTitle(string $title): FormManager
  {
    $this->title = $title;
    return $this;
  }

  /**
   * @return array
   */
  public function getFormFields(): array
  {
    return $this->formFields;
  }

  /**
   * @return string
   */
  public function getIndexRoute(): string
  {
    return $this->indexRoute;
  }

  /**
   * @param string $indexRoute
   * @return FormManager
   */
  public function setIndexRoute(string $indexRoute): FormManager
  {
    $this->indexRoute = $indexRoute;
    return $this;
  }

  /**
   * @return string
   */
  public function getDeleteRoute(): string
  {
    return $this->deleteRoute;
  }

  /**
   * @param string $deleteRoute
   * @return FormManager
   */
  public function setDeleteRoute(string $deleteRoute): FormManager
  {
    $this->deleteRoute = $deleteRoute;
    return $this;
  }

  /**
   * FormManager constructor.
   * @param array $formFields
   */
  public function __construct(array $formFields = [])
  {
    $this->formFields = $formFields;
  }

  /**
   * Check if form has been submitted
   * @param array $submits
   * @return bool
   */
  public static function isSubmitted($submits = [ "submitButton", "closeButton" ])
  {
    $submitted = false;
    foreach ($submits as $submit) {
      if (filter_has_var(INPUT_POST, $submit)) {
        $submitted = true;
        break;
      }
    }
    return $submitted;
  }


  /**
   * Add a FormField object to the collection
   * @param $props
   * @return FormManager
   */
  public function addField($props)
  {
    if (is_array($props)) {
      $field = new FormField();
      $field
        ->setName($props["name"] ?? "")
        ->setLabel($props["label"] ?? $props["name"])
        ->setFilter($props["filter"] ?? FILTER_SANITIZE_STRING)
        ->setRequired($props["required"] ?? false)
        ->setDefaultValue($props["defaultValue"] ?? "")
        ->setErrorMessage($props["errorMessage"] ?? ($props['label'] ?? $props["name"]) . " non saisi(e)")
        ->setControlType($props["controlType"] ?? "text")
        ->setCssClass($props["cssClass"] ?? FormField::getDefaultCSS($field->getControlType()))
        ->setPrimeKey($props["primeKey"] ?? false)
        ->setValueList($props["valueList"] ?? [])
        ->setSize($props["size"] ?? []);
      $this->formFields[$props["name"]] = $field;
    }
    return $this;
  }

  /**
   * Return the FormField object for a given field name
   * @param $name
   * @return FormField
   */
  public function getField($name)
  {
    return $this->formFields[$name];
  }

  /**
   * Check if form is valid
   * @return bool
   */
  public function isValid()
  {
    return (count($this->validateForm()) == 0);
  }

  /**
   * Check form fields and return a list of errors, if any
   * @return array
   */
  public function validateForm()
  {
    $errorList = [];

    foreach ($this->formFields as $field) {
      $fieldValue = filter_input(INPUT_POST, $field->getName(), $field->getFilter());
      if (trim($fieldValue) === "") {
        if ($field->isPrimeKey() || $field->isRequired()) {
          $errorList[] = $field->getErrorMessage();
        }
      }
    }

    return $errorList;
  }

  /**
   * Convert POSTed data to an associative array
   * @return array
   */
  public function getData()
  {

    $formData = [];
    foreach ($this->formFields as $field) {
      $fieldName = $field->getName();
      $fieldValue = filter_input(INPUT_POST, $fieldName, $field->getFilter());
      if ($field->getControlType() === "checkbox") {
        $fieldValue = $fieldValue ?? "0";
      }
      $formData[$fieldName] = $fieldValue;
    }

    return $formData;
  }

  /**
   * Convert POSTed data to an entity object
   * @param string $className
   * @return object
   */
  public function getEntity(string $className) : object
  {
    $entity = new $className();

    foreach ($this->getData() as $field => $value) {
      Tools::setProperty($entity, Tools::pascalize($field), $value);
    }

    return $entity;
  }

  /**
   * Generate HTML chunk for field
   * @param FormField $field
   * @param object $entity
   * @return false|string
   */
  private function renderHTML(FormField $field, ?object $entity)
  {
    $name = $field->getName();
    $value = null;
    if ($entity != null)
      $value = Tools::getProperty($entity, Tools::pascalize($name));
    return $field->render($value);
  }

  /**
   * Render all fields in the form
   * @param object $entity
   * @return string
   */
  public function render(?object $entity)
  {
    $formHTML = "";

    foreach ($this->formFields as $field) {
      $formHTML .= $this->renderHTML($field, $entity) . "\n";
    }

    return $formHTML;
  }

  /**
   * Render a field by its name
   * @param $fieldName
   * @param $entity
   * @return false|string
   */
  public function renderField(string $fieldName, ?object $entity)
  {
    $formHTML = "";

    foreach ($this->formFields as $field) {
      if ($field->getName() === $fieldName) {
        $formHTML = $this->renderHTML($field, $entity);
        break;
      }
    }

    return $formHTML;
  }

  /**
   * Render the bottom buttons bar
   * @param object $entity
   * @return false|string
   */
  public function renderButtons(?object $entity)
  {
    $empty = ($entity == null);

    if ($empty) {
      $options["submitButton"] = "Ajouter";
      $options["closeButton"] = "Ajouter & fermer";
    } else {
      $options["closeButton"] = "Valider";
    }

    if (!empty($this->indexRoute)) {
      $options["indexRoute"] = $this->indexRoute;
    }

    if (!empty($this->deleteRoute) && !$empty) {
      $options["deleteRoute"] = $this->deleteRoute;
    }

    ob_start();

    extract($options);

    require VIEWS_PATH . "/_fragments/form-buttons.phtml";

    return ob_get_clean();

  }

}