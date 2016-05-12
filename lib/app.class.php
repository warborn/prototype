<?php

class App {
  private static $router;
  private static $db;

  public static function run($url, $method) {
    self::$router = new Router();
    require_once(ROOT.DS.'config'.DS.'routes.php');
    $db_adapter = Config::get('db.adapter');
    self::$db = new $db_adapter(Config::get('db.host'),Config::get('db.user'), Config::get('db.password'), Config::get('db.db_name'));
    self::$router->dispatch($url, $method);
  }

  public static function get_db() {
    return self::$db;
  }

  public static function get_router() {
    return self::$router;
  }

}

?>
