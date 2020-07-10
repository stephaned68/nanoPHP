<?php


namespace framework;

use Exception;

class App
{
  /**
   * @var array
   */
  private array $config;

  /**
   * @var Router
   */
  public Router $router;

  /**
   * @var Dispatcher
   */
  public Dispatcher $dispatcher;

  /**
   * @return array
   */
  public function getConfig(): array
  {
    return $this->config;
  }

  /**
   * App constructor.
   */
  public function __construct()
  {
    if (file_exists("app.conf")) {
      // load database configuration
      $this->config = parse_ini_file("app.conf", true);
    } else {
      // set default database configuration
      $this->config = [
        "Runtime" => [
          "Mode" => "production"
        ],
        "Database" => [
          "Type" => "mysql",
          "Server" => "localhost",
          "Name" => "simple-fw", // set the full pathname of the .db file for SQLite
          "User" => "root",
          "Password" => ""
        ]
      ];
    }

    // define the database type
    define("DB_TYPE", $this->config["Database"]["Type"]);

    // define the database server
    define("DB_SERVER", $this->config["Database"]["Server"] ?? null);

    // define the database server port
    if (array_key_exists("Port", $this->config["Database"])) {
      define("DB_PORT", $this->config["Database"]["Port"]);
    } else {
      define("DB_PORT", null);
    }

    // define the database name or file path
    define("DB_NAME", $this->config["Database"]["Name"]);

    // define the database connection string for PDO
    $dsn = "";
    switch ($this->config["Database"]["Type"]) {
      case "mysql":
        $dsn = "mysql:host=" . DB_SERVER . ";port=" . (DB_PORT ?? "3306") . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        break;
      case "pgsql":
        $dsn = "pgsql:host=" . DB_SERVER . ";port=" . (DB_PORT ?? "5432") . ";dbname=" . DB_NAME;
        break;
      case "sqlite":
        $dsn = "sqlite:" . DATA_PATH . DIRECTORY_SEPARATOR . DB_NAME;
        break;
    }

    // define the database DSN
    define("DSN", $dsn);

    // define the database user/password
    define("DB_USER", $this->config["Database"]["User"] ?? null);
    define("DB_PASS", $this->config["Database"]["Password"] ?? null);

    // define the application name
    define("APP_NAME", $this->config["Global"]["AppName"] ?? "My Application Name");

    // define the Views base layout
    define("VIEWS_LAYOUT", "layout");

    $route = filter_input(INPUT_GET, "route", FILTER_SANITIZE_URL);
    if ($route == "") {
      $route = $_SERVER["PATH_INFO"] ?? "";
      $route = substr($route, 1);
    }

    $this->router = new Router($route);

    $this->dispatcher = new Dispatcher(
      $this->router,
      implode(DIRECTORY_SEPARATOR, [ "app", "controllers", "" ]),
      implode(DIRECTORY_SEPARATOR, [ "app", "models", "" ])
    );
  }

  /**
   * Run the application
   * @throws Exception
   */
  public function run() : void
  {
    try {
      $this->dispatcher->run();
    }
    catch (Exception $ex) {
      $mode = strtolower($this->config["Runtime"]["Mode"]) ?? "production";
      if ($mode == "development") {
        throw $ex;
      }
      else {
        $errorPage = new View();
        $errorPage->setVariable("errorInfo", $ex);
        $errorView = "error/" . $ex->getCode() ?? "500";
        echo $errorPage->render($errorView);
      }
    }
  }
}