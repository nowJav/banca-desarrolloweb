<?php
declare(strict_types=1);

// Front controller

// Composer autoload
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap app (env, session, routes config)
$app = require __DIR__ . '/../bootstrap/app.php';

// Resolve request method and path
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';

// Dispatch via router
$routes = is_array($app) && isset($app['routes']) ? $app['routes'] : null;
$router = new \App\core\Router($routes);
$router->dispatch($method, $path);
