<?php

class View {

  private $template_path;
  private $data;

  public function __construct($template_path, $data = null) {
    if(file_exists($template_path) && is_readable($template_path)) {
      $this->template_path = $template_path;
      $this->data = $data;
    } else {
      throw new \Exception('Template not found in: ' . $template_path);
    }
  }

  public function render() {
    ob_start();
    if(isset($this->data)) {
      extract($this->data);
    }
    require_once($this->template_path);
    return ob_get_clean();
  }

}

?>
