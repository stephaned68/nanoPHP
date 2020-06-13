<?php

namespace framework;


class View
{
  /**
   * @var string
   */
  private $layout;

  /**
   * @var array
   */
  private $data = [];

  /**
   * View constructor.
   * @param string $layout
   */
  public function __construct(string $layout = VIEWS_LAYOUT)
  {
    $this->layout = $layout;
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