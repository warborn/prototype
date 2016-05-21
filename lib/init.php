<?php

function camel_case_to_snake_case($input) {
  return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $input)), '_');
}

function render($partial, $options = null) {
  $allowed_extensions = ['html', 'php'];
  $found = false;
  $partial_path = ROOT.DS.'app'.DS.'views'.DS.$partial;
  foreach ($allowed_extensions as $extension) {
    if(file_exists($partial_path . ".{$extension}") && is_readable($partial_path. ".{$extension}")) {
      $found = true;
      if(isset($options['locals'])) {
        extract($options['locals']);
      }
      require_once($partial_path. ".{$extension}");
    }
  }
  if(! $found) {
    throw new \Exception('Missing partial or partial with invalid extension ' . $partial_path);
  }
}

function redirect_to($url) {
  header('Location: /' . $url);
  exit();
}

spl_autoload_register(function($class_name) {
  // Get libraries paths
  $lib_path = ROOT.DS.'lib'.DS.camel_case_to_snake_case($class_name).'.class.php';
  // Get controllers paths
  $controllers_path = ROOT.DS.'app'.DS.'controllers'.DS.camel_case_to_snake_case($class_name).'.php';
  // Get models paths
  $models_path = ROOT.DS.'app'.DS.'models'.DS.camel_case_to_snake_case($class_name).'.php';

  // Verifying the existence of the class files
  if(file_exists($lib_path)) {
    require_once($lib_path);
  } elseif(file_exists($controllers_path)) {
    require_once($controllers_path);
  } elseif(file_exists($models_path)) {
    require_once($models_path);
  } else {
    // throw new \Exception("Failed to include class: ".$class_name);
  }
});

// Requiring all the configurations for the application
require_once(ROOT.DS.'config'.DS.'config.php');

set_error_handler('Error::error_handler');
set_exception_handler('Error::exception_handler');

?>
