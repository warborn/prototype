<?php

class ActionController {

  protected $params = [];

  public function __construct($route_params) {
    array_shift($_GET);
    $this->params = array_merge($route_params, $_GET);
  }

}

?>
