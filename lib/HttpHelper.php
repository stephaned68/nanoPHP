<?php


namespace framework;


class HttpHelper
{
  /**
   * @var int HTTP 200
   */
  public static int $STATUS_OK = 200;

  /**
   * @var int HTTP 201
   */
  public static int $STATUS_CREATED = 201;

  /**
   * @var int HTTP 204
   */
  public static int $STATUS_EMPTY = 204;

  /**
   * @var int HTTP 400
   */
  public static int $STATUS_BADREQUEST = 400;

  /**
   * @var int HTTP 401
   */
  public static int $STATUS_UNAUTHORIZED = 401;

  /**
   * @var int HTTP 403
   */
  public static int $STATUS_FORBIDDEN = 403;

  /**
   * @var int HTTP 404
   */
  public static int $STATUS_NOTFOUND = 404;

  /**
   * @var int HTTP 500
   */
  public static int $STATUS_ERROR = 500;
}