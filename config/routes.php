<?php

$router = App::get_router();

$router->add('', 'GET', ['to' => 'pages#index']);
$router->resources('posts');

?>
