<?php

namespace framework;

class Dispatcher
{
  /**
   * @var Router
   */
  private $router;

  /**
   * @var string
   */
  private $nameSpace;

  /**
   * Dispatcher constructor.
   * @param Router $router
   * @param string $nameSpace
   */
  public function __construct(Router $router, string $nameSpace = "")
  {
    $this->router = $router;
    $this->nameSpace = $nameSpace;
  }

  /**
   * Call the action method on the controller
   */
  public function run()
  {
    $className = $this->nameSpace . $this->router->getControllerName();
    $controllerInstance = new $className();

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