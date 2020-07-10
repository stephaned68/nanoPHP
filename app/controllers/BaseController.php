<?php

namespace app\controllers;

use framework\View;

abstract class BaseController
{
  /**
   * @var View
   */
  private View $view;

  /**
   * @var array
   */
  private array $queryParams = [];

  /**
   * @var array
   */
  private array $postData = [];

  /**
   * @return View
   */
  public function getView(): View
  {
    return $this->view;
  }

  /**
   * @param $layout
   * @return BaseController
   */
  public function setView($layout = VIEWS_LAYOUT): BaseController
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
   * @param $name
   * @return mixed|null
   */
  public function getQueryParam($name)
  {
    return $this->queryParams[$name] ?? null;
  }

  /**
   * @param array $queryParams
   * @return BaseController
   */
  public function setQueryParams(array $queryParams): BaseController
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
   * @param $name
   * @return mixed|null
   */
  public function getPostField($name)
  {
    return $this->postData[$name] ?? null;
  }

  /**
   * @param array $postData
   * @return BaseController
   */
  public function setPostData(array $postData): BaseController
  {
    $this->postData = $postData;
    return $this;
  }

  /**
   * Render the view
   * @param $template
   * @param array $data
   */
  protected function render($template, $data = [])
  {
    echo $this->view->render($template, $data);
  }

}