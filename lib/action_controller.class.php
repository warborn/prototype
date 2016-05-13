<?php

class ActionController {

  protected $params = [];
  protected $layout = "application";
  protected $resource;

  public function __construct($route_params) {
    array_shift($_GET);
    if(isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
      $json = json_decode(file_get_contents('php://input'), true);
      $_POST = isset($json)? array_merge($_POST, $json) : $_POST;
    }
    $this->params = array_merge($route_params, $_GET, $_POST);
  }

  public function get_layout() {
    return $this->layout;
  }

  protected function need($resource) {
    if(isset($this->params[$resource]) && !empty($this->params[$resource])) {
      $this->resource = $resource;
      return $this;
    }
    throw new \Exception(sprintf('There is no %s param on the request', $resource));
  }

  protected function permit() {
    if(isset($this->resource)) {
      $permited_attributes = array();
      foreach(func_get_args() as $attribute) {
        if(isset($this->params[$this->resource][$attribute])) {
          $permited_attributes[$attribute] = $this->params[$this->resource][$attribute];
        }
      }
      return $permited_attributes;
    }
    throw new \Exception(sprintf('require wasn\'t called or was called without arguments'));
  }

  protected function redirect_to($action) {
    $locations = ['index' => 'air-ballons'];
    header('Location: /' . $locations[$action]);
    exit();
  }

}

?>
