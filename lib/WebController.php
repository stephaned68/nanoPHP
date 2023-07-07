<?php

namespace framework;

/**
 * Base functionality for a web controller
 * Class WebController
 * @package app\controllers
 */
abstract class WebController
{
  /**
   * @var FormManager
   */
  protected FormManager $form;

  /**
   * @var View View engine
   */
  protected View $view;

  /**
   * @var array Query string parameters
   */
  protected array $queryParams = [];

  /**
   * @var array POSTed data
   */
  protected array $postData = [];

  /**
   * @return View
   */
  public function getView(): View
  {
    return $this->view;
  }

  /**
   * @param string $layout Base page layout
   * @return WebController
   */
  public function setView(string $layout = VIEWS_LAYOUT): WebController
  {
    $this->view = new View($layout);
    return $this;
  }

  /**
   * @return array
   */
  public function getQueryParams(): array
  {
    return $this->queryParams;
  }

  /**
   * @param string $name Query string parameter to retrieve
   * @return mixed
   */
  public function getQueryParam(string $name): mixed
  {
    return $this->queryParams[$name] ?? null;
  }

  /**
   * @param array $queryParams
   * @return WebController
   */
  public function setQueryParams(array $queryParams): WebController
  {
    $this->queryParams = $queryParams;
    return $this;
  }

  /**
   * @return array
   */
  public function getPostData(): array
  {
    return $this->postData;
  }

  /**
   * @param string $name POST field to retrieve
   * @return mixed
   */
  public function getPostField(string $name): mixed
  {
    return $this->postData[$name] ?? null;
  }

  /**
   * @param array $postData
   * @return WebController
   */
  public function setPostData(array $postData): WebController
  {
    $this->postData = $postData;
    return $this;
  }

  /**
   * Render the view
   * @param string $template Template name to render
   * @param array $data
   */
  protected function render(string $template, array $data = []): void
  {
    echo $this->view->render($template, $data);
  }

}