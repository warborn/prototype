<?php

class View {

  private $template_path;

  public function __construct($template_path = null) {
    if(file_exists($template_path) && is_readable($template_path)) {
      $this->template_path = $template_path;
    } else {
      throw new \Exception('Template not found in: ' . $template_path);
    }
  }

  public function render() {
    ob_start();
    require_once($this->template_path);

    return ob_get_clean();
  }

}

?>
