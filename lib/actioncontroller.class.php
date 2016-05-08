<?php

class ActionController {

  protected $params = [];
  protected $layout = "application";

  public function __construct($route_params) {
    array_shift($_GET);
    $this->params = array_merge($route_params, $_GET);
  }

  public function get_layout() {
    return $this->layout;
  }

}

?>
