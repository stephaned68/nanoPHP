<?php

namespace framework;

/**
 * Represents an HTML form field
 * Class FormField
 * @package framework
 */
class FormField
{

  /**
   * @var string|null Field name (used for name='' and id='')
   */
  private ?string $name;

  /**
   * @var string|null
   */
  private ?string $label;

  /**
   * @var int Input sanitizer/validator
   */
  private int $filter;

  /**
   * @var bool
   */
  private bool $required;

  /**
   * @var mixed
   */
  private ?string $defaultValue;

  /**
   * @var string|null
   */
  private ?string $errorMessage;

  /**
   * @var string|null
   */
  private ?string $controlType;

  /**
   * @var string|null
   */
  private ?string $cssClass;

  /**
   * @var bool
   */
  private bool $primeKey;

  /**
   * @var array
   */
  private array $valueList;

  /**
   * @var array
   */
  private array $size;

  /**
   * FormField constructor.
   * @param array $options
   */
  public function __construct(array $options) {
    foreach ($options as $k => $v) {
      $this->$k = $v;
    }
  }

  /**
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @param string $name
   * @return FormField
   */
  public function setName(string $name): FormField
  {
    $this->name = $name;
    return $this;
  }

  /**
   * @return string
   */
  public function getLabel(): string
  {
    return $this->label;
  }

  /**
   * @param string $label
   * @return FormField
   */
  public function setLabel(string $label): FormField
  {
    $this->label = $label;
    return $this;
  }

  /**
   * @return int
   */
  public function getFilter(): int
  {
    return $this->filter;
  }

  /**
   * @param int $filter
   * @return FormField
   */
  public function setFilter(int $filter): FormField
  {
    $this->filter = $filter;
    return $this;
  }

  /**
   * @return bool
   */
  public function isRequired(): bool
  {
    return $this->required;
  }

  /**
   * @param bool $required
   * @return FormField
   */
  public function setRequired(bool $required): FormField
  {
    $this->required = $required;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDefaultValue(): mixed
  {
    return $this->defaultValue;
  }

  /**
   * @param mixed $defaultValue
   * @return FormField
   */
  public function setDefaultValue(mixed $defaultValue): FormField
  {
    $this->defaultValue = $defaultValue;
    return $this;
  }

  /**
   * @return string
   */
  public function getErrorMessage(): string
  {
    return $this->errorMessage;
  }

  /**
   * @param string $errorMessage
   * @return FormField
   */
  public function setErrorMessage(string $errorMessage): FormField
  {
    $this->errorMessage = $errorMessage;
    return $this;
  }

  /**
   * @return string
   */
  public function getControlType(): string
  {
    return $this->controlType;
  }

  /**
   * @param string $controlType
   * @return FormField
   */
  public function setControlType(string $controlType): FormField
  {
    $this->controlType = strtolower($controlType);
    return $this;
  }

  /**
   * @return string
   */
  public function getCssClass(): string
  {
    return $this->cssClass;
  }

  /**
   * @param string $cssClass
   * @return FormField
   */
  public function setCssClass(string $cssClass): FormField
  {
    $this->cssClass = $cssClass;
    return $this;
  }

  /**
   * @return bool
   */
  public function isPrimeKey(): bool
  {
    return $this->primeKey;
  }

  /**
   * @param bool $primeKey
   * @return FormField
   */
  public function setPrimeKey(bool $primeKey): FormField
  {
    $this->primeKey = $primeKey;
    return $this;
  }

  /**
   * @return array
   */
  public function getValueList(): array
  {
    return $this->valueList;
  }

  /**
   * @param array $valueList
   * @return FormField
   */
  public function setValueList(array $valueList): FormField
  {
    $this->valueList = $valueList;
    return $this;
  }

  /**
   * @return array
   */
  public function getSize(): array
  {
    return $this->size;
  }

  /**
   * @param array $size
   * @return FormField
   */
  public function setSize(array $size): FormField
  {
    $this->size = $size;
    return $this;
  }

  /**
   * Render the field's HTML
   * @param $data
   * @return string
   */
  public function render($data): string
  {

    if ($this->controlType === "hidden") {
      return '<input type="hidden" name="' . $this->name . '" value="' . $data . '">';
    }

    $options = [
      "fieldName" => $this->name,
      "fieldLabel" => $this->label ?? $this->name,
      "fieldType" => $this->controlType ?? "text",
      "fieldClass" => self::getDefaultCSS($this->controlType) . " " . $this->cssClass ?? "",
      "fieldSelect" => $this->valueList ?? [],
      "fieldSize" => $this->size,
      "fieldValue" => $data ?? $this->defaultValue,
      "fieldRequired" => $this->required ? 'required' : ''
    ];

    if ($this->primeKey && $data != null) {
      $options["fieldClass"] = "form-control-plaintext";
      $options["fieldReadonly"] = "readonly";
    }

    ob_start();

    extract($options);

    if (
      $this->controlType === "select"
      || $this->controlType === "textarea"
      || $this->controlType === "checkbox"
    ) {
      $component = COMPONENTS_PATH .  "/form-{$this->controlType}.html.php";
      require $component;
    } else {
      require COMPONENTS_PATH . "/form-group.html.php";
    }

    return ob_get_clean();
  }

  /**
   * Return the default CSS class for the control
   * @param $controlType
   * @return string
   */
  public static function getDefaultCSS($controlType): string
  {
    $defaultCSS = [
      "hidden" => "",
      "checkbox" => "form-check-input"
    ];

    return $defaultCSS[$controlType] ?? "form-control";
  }

}
