<?php

function camel_case_to_snake_case($input) {
  return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $input)), '_');
}

function render($partial) {
  require_once(ROOT.DS.'app'.DS.'views'.DS.$partial.'.html');
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
