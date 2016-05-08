<?php

// Constants required for file path localization
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__));

require_once(ROOT.DS.'lib'.DS.'init.php');

$post_controller = new PostsController();
$post_model = new Post();

echo ROOT;

?>
