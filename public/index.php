<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Router;
use Laminas\Diactoros\ServerRequestFactory;

$request = ServerRequestFactory::fromGlobals();

// Подгрузка маршрутов
$router = require __DIR__ . '/../src/routes.php';

// Обработка запроса
$response = $router->dispatch($request);

// Отправка ответа
(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
