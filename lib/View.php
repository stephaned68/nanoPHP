<?php

namespace framework;

/**
 * Represents a page
 * Class View
 * @package framework
 */
class View
{
  /**
   * @var string Base layout
   */
  private string $layout;

  /**
   * @var object
   */
  private ?object $viewModel;

  /**
   * @var array
   */
  private array $data = [];

  /**
   * View constructor.
   * @param string $layout
   * @param object|null $viewModel
   */
  public function __construct(string $layout = VIEWS_LAYOUT, object $viewModel = null)
  {
    $this->layout = $layout;
    $this->viewModel = $viewModel;
  }

  /**
   * @return string
   */
  public function getLayout(): string
  {
    return $this->layout;
  }

  /**
   * @param string $layout
   * @return View
   */
  public function setLayout(string $layout): View
  {
    $this->layout = $layout;
    return $this;
  }

  /**
   * @return object
   */
  public function getViewModel(): object
  {
    return $this->viewModel;
  }

  /**
   * @param object $viewModel
   * @return View
   */
  public function setViewModel(object $viewModel): View
  {
    $this->viewModel = $viewModel;
    return $this;
  }

  /**
   * @return array
   */
  public function getData(): array
  {
    return $this->data;
  }

  /**
   * @param array $data
   * @return View
   */
  public function setData(array $data): View
  {
    $this->data = $data;
    return $this;
  }

  /**
   * Set a single variable value
   * @param string $name Variable name
   * @param mixed $value Variable value
   * @return View
   */
  public function setVariable(string $name, $value): View
  {
    $this->data[$name] = $value;
    return $this;
  }

  /**
   * Return view from template merged with data
   * @param $template
   * @param array $data
   * @return false|string
   */
  private function getTemplateContent($template, $data = [])
  {
    if (count($data) > 0) {
      $this->data = array_merge($this->data, $data);
    }

    if ($this->viewModel != null) {
      $this->data[] = [ "model" => $this->viewModel ];
    }

    ob_start();

    extract($this->data);

    require_once VIEWS_PATH . "/{$template}.phtml";

    return ob_get_clean();
  }

  /**
   * Render a view
   * @param $template
   * @param array $data
   * @return false|string
   */
  public function render($template, $data = [])
  {
    $pageContent = $this->getTemplateContent($template, $data);
    $data["content"] = $pageContent;

    // insert specific javascript file
    // path schema is 'public/scripts/{controller}/{action}.js'
    $script = "scripts/$template.js";
    if (!file_exists(PUBLIC_PATH . "/" . $script)) {
      $script = "";
    }
    $data["script"] = $script;

    if (empty($this->layout)) {
      return $pageContent;
    } else {
      return $this->getTemplateContent($this->layout, $data);
    }
  }

}