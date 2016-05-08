<?php

class PostsController extends ActionController {

  public function index() {
    // print_r($this->params);
  }

  public function show() {
    // print_r($this->params);
    $post = 'This is the first post!!!!!!!!!!!!!!!!!!!!';

    return ['post' => $post];
  }
}

?>
