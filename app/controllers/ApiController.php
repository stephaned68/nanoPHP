<?php


namespace app\controllers;


use framework\Tools;

abstract class ApiController
{
  /**
   * @var int
   */
  protected int $responseCode = 200;

  /**
   * @var array
   */
  protected array $queryParams = [];

  /**
   * @param int $responseCode
   * @return ApiController
   */
  public function setResponseCode(int $responseCode): ApiController
  {
    $this->responseCode = $responseCode;
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
   * @return ApiController
   */
  public function setQueryParams(array $queryParams): ApiController
  {
    $this->queryParams = $queryParams;
    return $this;
  }

  /**
   * Load JSON request
   * @param bool $asArray
   * @return array
   */
  protected function getRequest(bool $asArray = true)
  {
    return json_decode(file_get_contents("php://input"), $asArray);
  }

  protected function loadEntity(string $className, array $data = []) : object
  {
    if (count($data) == 0)
      $data = $this->getRequest();

    $entity = new $className();
    foreach ($this->getRequest() as $key => $value) {
      Tools::setProperty($entity, $key, $value);
    }

    return $entity;
  }

  /**
   * Output JSON response
   * @param $response
   */
  protected function sendResponse($response) : void
  {
    if ($this->responseCode != 200)
      http_response_code($this->responseCode);
    echo json_encode($response);
  }

  abstract public function doGet($id = null);

  abstract public function doPost();

  abstract public function doPut($id);

  abstract public function doDelete($id);
}