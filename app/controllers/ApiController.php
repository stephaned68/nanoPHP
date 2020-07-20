<?php


namespace app\controllers;


use framework\Tools;

/**
 * Base functionality for an API controller
 * Class ApiController
 * @package app\controllers
 */
abstract class ApiController
{
  /**
   * @var int HTTP status code
   */
  protected int $responseCode = 200;

  /**
   * @var array Query string parameters
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
   * @param string $name Query string parameter to return
   * @return mixed|null
   */
  public function getQueryParam(string $name)
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

  /**
   * Load an entity class from an associative array
   * @param string $className Entity class name
   * @param array $data Associative array with data to load
   * (Uses the decoded JSON body if not passed in)
   * @return object
   */
  protected function loadEntity(string $className, array $data = []) : object
  {
    if (count($data) == 0)
      $data = $this->getRequest();

    $entity = new $className();
    foreach ($data as $key => $value) {
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

  /**
   * GET /api/entity - returns all entities
   * GET /api/entity/:id - returns one entity
   * @param mixed $id Record identifier to return
   * @return mixed
   */
  abstract public function doGet($id = null);

  /**
   * POST /api/entity - add a new entity
   * @return mixed
   */
  abstract public function doPost();

  /**
   * PUT /api/entity/:id - update an entity
   * @param $id
   * @return mixed
   */
  abstract public function doPut($id);

  /**
   * DELETE /api/entity/:id - delete an entity
   * @param $id
   * @return mixed
   */
  abstract public function doDelete($id);
}