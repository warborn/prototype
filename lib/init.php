<?php

spl_autoload_register(function($class_name) {
  // Get libraries paths
  $lib_path = ROOT.DS.'lib'.DS.strtolower($class_name).'.class.php';
  // Get controllers paths
  $controllers_path = ROOT.DS.'app'.DS.'controllers'.DS.str_replace('controller', '_controller', strtolower($class_name)).'.php';
  // Get models paths
  $models_path = ROOT.DS.'app'.DS.'models'.DS.strtolower($class_name).'.php';

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

set_error_handler('Error::error_handler');
set_exception_handler('Error::exception_handler');

?>
