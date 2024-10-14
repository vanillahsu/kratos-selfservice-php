<?php declare(strict_types=1);

$router = $di->getRouter();

// Define your routes here

$router->addGet('/error', 'index::error');
$router->addGet('/login', 'index::login');
$router->addGet('/registration', 'index::registration');

$router->handle($_SERVER['REQUEST_URI']);
