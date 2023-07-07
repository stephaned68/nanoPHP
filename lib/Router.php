<?php

namespace framework;

/**
 * Class Router
 * @package framework
 */
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
   * @var string
   */
  private string $actionVerbName = "indexGetAction";

  /**
   * @var string
   */
  private string $apiActionName = "processGet";

  /**
   * @var array
   */
  private array $actionParameters = [];

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
  public function __construct(string $route)
  {
    $this->route = $route;

    $routeSegments = explode("/", $route);

    // check if URL is like /api/...
    if (count($routeSegments) > 0 && !empty(trim($routeSegments[0]))) {
      if (strtolower($routeSegments[0]) == "api") {
        array_shift($routeSegments);
        $this->actionName = "api";
      }
    }

    // get entity part (/entity/action or /api/entity)
    if (count($routeSegments) > 0 && !empty(trim($routeSegments[0]))) {
      $entity = Tools::pascalize(array_shift($routeSegments));
      $this->controllerName = $entity . "Controller";
      $this->apiController = Tools::pluralize($entity) . "Controller";
      $this->repositoryName = $entity . "Repository";
    }

    // get action part (/entity/action)
    $verb = ucfirst($_SERVER["REQUEST_METHOD"]);
    if ($this->actionName != "api") {
      if (count($routeSegments) > 0 && !empty(trim($routeSegments[0]))) {
        $action = Tools::camelize(array_shift($routeSegments));
        $this->actionName = $action . "Action";
        $this->actionVerbName = $action . $verb . "Action";
      }
    } else {
      $this->apiActionName = "process" . $verb;
    }

    // get action parameters (/entity/action/.../...)
    if (count($routeSegments) > 0 && !empty(trim($routeSegments[0]))) {
      array_map(function ($item) {
        return urldecode($item);
      }, $routeSegments);
      $this->actionParameters = $routeSegments;
    }

    // check if URL is like index.php?route=/entity/action/...
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
  public function getRoute(): string
  {
    return $this->route;
  }

  /**
   * @return string
   */
  public function getControllerName(): string
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
  public function getActionName(): string
  {
    return $this->actionName;
  }

  /**
   * @return string
   */
  public function getActionVerbName(): string
  {
    return $this->actionVerbName;
  }

  /**
   * @return string
   */
  public function getApiActionName(): string
  {
    return $this->apiActionName;
  }

  /**
   * @return array
   */
  public function getActionParameters(): array
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
  public static function route(array $args = [], array $query = []): string
  {
    $url = "/";
    if (count($args) > 0) {
      foreach ($args as $argK => $argV) {
        $args[$argK] = urlencode(trim($argV ?? ""));
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
  public static function redirectTo(array $args = []): void
  {
    header("Location: " . self::route($args));
  }

}