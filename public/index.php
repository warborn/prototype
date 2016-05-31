<?php

// Constants required for file path localization
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__));

require_once(ROOT.DS.'lib'.DS.'init.php');

if(isset($_POST['_method'])) {
  $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
}
App::run($_SERVER['QUERY_STRING'], $_SERVER['REQUEST_METHOD']);

?>
