<?php declare(strict_types=1);

$router = $di->getRouter();

// Define your routes here

$router->addGet('/error', 'index::error');
$router->addGet('/login', 'index::login');
$router->addGet('/recovery', 'index::recovery');
$router->addGet('/registration', 'index::registration');
$router->addGet('/verification', 'index::verification');
$router->addGet('/welcome', 'index::welcome');

$router->handle($_SERVER['REQUEST_URI']);
