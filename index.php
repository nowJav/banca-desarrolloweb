<?php
declare(strict_types=1);

// Front controller at project root (for hosting where document root is this folder)

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';

$routes = is_array($app) && isset($app['routes']) ? $app['routes'] : null;
$router = new \App\core\Router($routes);
$router->dispatch($method, $path);

