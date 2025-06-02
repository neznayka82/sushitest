<?php

use App\Controllers\CatController;
use App\Router;

$router = new Router();
$controller = new CatController();

// Регистрация маршрутов
$router->addRoute('GET', '/cat/search', [$controller, 'search']);
$router->addRoute('GET', '/cat/create', [$controller, 'create']);
$router->addRoute('POST', '/cat/{id}/edit', [$controller, 'edit']);
$router->addRoute('POST', '/cat/storage', [$controller, 'storage']);
$router->addRoute('POST', '/cat/{id}/delete', [$controller, 'delete']);
$router->addRoute('GET', '/cat/{id}', [$controller, 'view']);
$router->addRoute('GET', '/', [$controller, 'index']);
$router->addRoute('GET', '/cats', [$controller, 'index']);

return $router;