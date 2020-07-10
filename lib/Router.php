<?php

namespace framework;

class Router
{

  /**
   * @var string
   */
  private string $route;

  /**
   * @var string
   */
  private string $controllerName = "HomeController";

  /**
   * @var string
   */
  private string $actionName = "indexAction";

  /**
   * @var string
   */
  private string $actionGet = "getIndex";

  /**
   * @var string
   */
  private string $actionPost = "postIndex";

  /**
   * @var array
   */
  private $actionParameters = [];

  /**
   * @var array
   */
  private array $queryParams = [];

  /**
   * @var array
   */
  private array $postData = [];

  /**
   * @var string
   */
  private string $repositoryName = "HomeRepository";

  /**
   * Router constructor.
   * @param string $route
   */
  public function __construct($route)
  {
    $this->route = $route;

    $routeParts = explode("/", $route);

    if (count($routeParts) > 0 && !empty(trim($routeParts[0]))) {
      $entity = Tools::pascalize(array_shift($routeParts));
      $this->controllerName = $entity . "Controller";
      $this->repositoryName = $entity . "Repository";
    }

    if (count($routeParts) > 0 && !empty(trim($routeParts[0]))) {
      $action = array_shift($routeParts);
      $this->actionName = Tools::camelize($action) . "Action";
      $this->actionGet = "get" . Tools::pascalize($action);
      $this->actionPost = "post" . Tools::pascalize($action);
    }

    if (count($routeParts) > 0 && !empty(trim($routeParts[0]))) {
      array_map(function ($item) {
        return urldecode($item);
      }, $routeParts);
      $this->actionParameters = $routeParts;
    }

    $queryParams = $_GET;
    if (array_key_first($queryParams) == "route") {
      array_shift($queryParams);
    }
    $this->queryParams = $queryParams;
    $this->postData = $_POST;

  }

  /**
   * @return string
   */
  public function getRoute()
  {
    return $this->route;
  }

  /**
   * @return string
   */
  public function getControllerName()
  {
    return $this->controllerName;
  }

  /**
   * @return string
   */
  public function getActionName()
  {
    return $this->actionName;
  }

  /**
   * @return string
   */
  public function getActionGet(): string
  {
    return $this->actionGet;
  }

  /**
   * @return string
   */
  public function getActionPost(): string
  {
    return $this->actionPost;
  }

  /**
   * @return array
   */
  public function getActionParameters()
  {
    return $this->actionParameters;
  }

  /**
   * @return array
   */
  public function getQueryParams(): array
  {
    return $this->queryParams;
  }

  /**
   * @return array
   */
  public function getPostData(): array
  {
    return $this->postData;
  }

  /**
   * @return string
   */
  public function getRepositoryName(): string
  {
    return $this->repositoryName;
  }

  /**
   * Generate a route URL
   * @param array $args
   * @param array $query
   * @return string
   */
  public static function route($args = [], $query = [])
  {
    $url = "/";
    if (count($args) > 0) {
      foreach ($args as $argK => $argV) {
        $args[$argK] = urlencode(trim($argV));
      }
      $url .= implode("/", $args);
    }
    if (count($query) > 0) {
      $queryArgs = [];
      foreach ($query as $queryK => $queryV) {
        $queryArgs[] = $queryK . "=" . urlencode(trim($queryV));
      }
      $url .= "&" . implode("&", $queryArgs);
    }
    return $url;
  }

  /**
   * Redirect to a route
   * @param array $args
   */
  public static function redirectTo($args = [])
  {
    header("Location: " . self::route($args));
  }

}