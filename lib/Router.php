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
  private string $apiController;

  /**
   * @var string
   */
  private string $actionName = "indexAction";

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
      if (strtolower($routeParts[0]) == "api") {
        array_shift($routeParts);
        $this->actionName = "api";
      }
    }

    if (count($routeParts) > 0 && !empty(trim($routeParts[0]))) {
      $entity = Tools::pascalize(array_shift($routeParts));
      $this->controllerName = $entity . "Controller";
      $this->apiController = Tools::pluralize($entity) . "Controller";
      $this->repositoryName = $entity . "Repository";
    }

    if ($this->actionName != "api") {
      if (count($routeParts) > 0 && !empty(trim($routeParts[0]))) {
        $action = array_shift($routeParts);
        $this->actionName = Tools::camelize($action) . "Action";
      }
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
  public function getApiController(): string
  {
    return $this->apiController;
  }

  /**
   * @return string
   */
  public function getActionName()
  {
    return $this->actionName;
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