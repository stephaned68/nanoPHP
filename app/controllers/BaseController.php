<?php

namespace app\controllers;

use framework\View;

/**
 * Base functionality for a web controller
 * Class BaseController
 * @package app\controllers
 */
abstract class BaseController
{
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
   * @return BaseController
   */
  public function setView(string $layout = VIEWS_LAYOUT): BaseController
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
   * @return mixed|null
   */
  public function getQueryParam(string $name)
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
   * @param string $name POST field to retrieve
   * @return mixed|null
   */
  public function getPostField(string $name)
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
   * @param string $template Template name to render
   * @param array $data
   */
  protected function render(string $template, array $data = [])
  {
    echo $this->view->render($template, $data);
  }

}