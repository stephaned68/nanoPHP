<?php

namespace framework;

use Error;
use Exception;

/**
 * Class Dispatcher
 * @package framework
 */
class Dispatcher
{
  /**
   * @var Router
   */
  private Router $router;

  /**
   * Dispatcher constructor.
   * @param Router $router
   */
  public function __construct(Router $router)
  {
    $this->router = $router;
  }

  /**
   * Call the action method on the controller
   * @throws Exception
   */
  public function run()
  {
    $controllerClass = "app\\controllers\\" . $this->router->getControllerName();
    $repositoryClass = "app\\models\\" . $this->router->getRepositoryName();

    $action = $this->router->getActionName(); // e.g. indexAction()

    // if URL is /api/entity
    if ($action == "api")
      $controllerClass = "app\\controllers\\" . $this->router->getApiController();

    // See if Repository class exists
    $repositoryInstance = null;
    if (class_exists($repositoryClass))
      $repositoryInstance = new $repositoryClass();

    // Instantiate controller class
    try {
      $controllerInstance = new $controllerClass($repositoryInstance);
    }
    catch (Error $ex) {
      throw new Exception("Route error - " . $ex->getMessage(), 404);
    }

    if (is_subclass_of($controllerInstance, "app\controllers\BaseController")) {
      $controllerInstance->setView();
      $controllerInstance->setQueryParams($this->router->getQueryParams());
      $controllerInstance->setPostData($this->router->getPostData());
    }

    if (is_subclass_of($controllerInstance, "app\controllers\ApiController")) {
      $controllerInstance->setQueryParams($this->router->getQueryParams());
    }

    // Method is either <functionName>Action or do<httpVerb>
    if ($action == "api") {
      $method = "do" . ucfirst($_SERVER["REQUEST_METHOD"]);
      if (method_exists($controllerInstance, $method)) {
        $action = $method;
      }
    }

    // Call the action method
    call_user_func_array(
      [
        $controllerInstance,
        $action
      ],
      $this->router->getActionParameters()
    );
  }

}