<?php

namespace framework;

class FormManager
{

  /**
   * @var string
   */
  private $title;

  /**
   * @var array
   */
  private $formFields;

  /**
   * @var string
   */
  private $indexRoute;

  /**
   * @var string
   */
  private $deleteRoute;

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
  public static function isSubmitted($submits = ["submit", "close"])
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
          array_push($errorList, $field->getErrorMessage());
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
      $formData[$fieldName] = addslashes($fieldValue);
    }

    return $formData;
  }

  /**
   * Generate HTML chunk for field
   * @param FormField $field
   * @param $data
   * @return false|string
   */
  private function renderHTML(FormField $field, $data)
  {
    $name = $field->getName();
    $value = null;
    if (array_key_exists($name, $data)) {
      $value = $data[$name];
    }
    return $field->render($value);
  }

  /**
   * Render all fields in the form
   * @param array $data
   * @return string
   */
  public function render($data = [])
  {
    $formHTML = "";

    foreach ($this->formFields as $field) {
      $formHTML .= $this->renderHTML($field, $data) . "\n";
    }

    return $formHTML;
  }

  /**
   * Render a field by its name
   * @param $fieldName
   * @param array $data
   * @return false|string
   */
  public function renderField($fieldName, $data = [])
  {
    $formHTML = "";

    foreach ($this->formFields as $field) {
      if ($field->getName() === $fieldName) {
        $formHTML = $this->renderHTML($field, $data);
        break;
      }
    }

    return $formHTML;
  }

  /**
   * Render the bottom buttons bar
   * @param array $data
   * @return false|string
   */
  public function renderButtons($data = [])
  {
    $empty = (count($data) == 0);

    if ($empty) {
      $options["btnSubmit"] = "Ajouter";
      $options["btnClose"] = "Ajouter & fermer";
    } else {
      $options["btnClose"] = "Modifier";
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