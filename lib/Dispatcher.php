<?php

namespace framework;

use Error;
use Exception;

class Dispatcher
{
  /**
   * @var Router
   */
  private Router $router;

  /**
   * @var string
   */
  private string $ctrlNameSpace;

  private string $modelNameSpace;

  /**
   * Dispatcher constructor.
   * @param Router $router
   * @param string $ctrlNameSpace
   * @param string $modelNameSpace
   */
  public function __construct(
    Router $router,
    string $ctrlNameSpace = "",
    string $modelNameSpace = ""
  )
  {
    $this->router = $router;
    $this->ctrlNameSpace = $ctrlNameSpace;
    $this->modelNameSpace = $modelNameSpace;
  }

  /**
   * Call the action method on the controller
   * @throws Exception
   */
  public function run()
  {
    $controllerClass = $this->ctrlNameSpace . $this->router->getControllerName();
    $repositoryClass = $this->modelNameSpace . $this->router->getRepositoryName();

    $repositoryInstance = null;
    if (class_exists($repositoryClass))
      $repositoryInstance = new $repositoryClass();

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

    // Method is either <functionName>Action
    $action = $this->router->getActionName(); // e.g. indexAction()
    // or <httpVerb><functionName>
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
      $method = $this->router->getActionGet(); // e.g. getIndex()
    } elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
      $method = $this->router->getActionPost(); // e.g. postIndex()
    } else {
      $method = $action;
    }
    if (method_exists($controllerInstance, $method)) {
      $action = $method;
    }

    call_user_func_array(
      [
        $controllerInstance,
        $action
      ],
      $this->router->getActionParameters()
    );
  }

}