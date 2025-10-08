<?php
declare(strict_types=1);

namespace App\core;

use App\config\Routes as RoutesConfig;

class Router
{
    private array $routes;

    public function __construct(?array $routes = null)
    {
        $this->routes = $routes ?? RoutesConfig::all();
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $path = rtrim($path, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && ($route['path'] === $path)) {
                if (!$this->runMiddleware($route)) {
                    return;
                }
                $this->invokeHandler($route['handler']);
                return;
            }
        }

        http_response_code(404);
        echo '404 Not Found';
    }

    private function invokeHandler(string $handler): void
    {
        [$controller, $action] = explode('@', $handler, 2);
        $fqcn = 'App\\Controllers\\' . $controller;

        if (!class_exists($fqcn)) {
            http_response_code(500);
            echo "Controller {$fqcn} not found";
            return;
        }

        $instance = new $fqcn();
        if (!method_exists($instance, $action)) {
            http_response_code(500);
            echo "Action {$action} not found in {$fqcn}";
            return;
        }

        $instance->$action();
    }

    private function runMiddleware(array $route): bool
    {
        if (!isset($route['middleware'])) {
            return true;
        }

        $middlewares = is_array($route['middleware']) ? $route['middleware'] : [$route['middleware']];
        foreach ($middlewares as $mw) {
            $class = $this->resolveMiddlewareClass($mw);
            if (!$class || !class_exists($class)) {
                continue;
            }
            $instance = new $class();
            if (method_exists($instance, 'handle')) {
                $ok = $instance->handle();
                if ($ok !== true) {
                    // Middleware may already have redirected; ensure fallback redirect
                    if (!headers_sent()) {
                        header('Location: /login', true, 302);
                    }
                    return false;
                }
            }
        }
        return true;
    }

    private function resolveMiddlewareClass(string $mw): ?string
    {
        // Allow short names
        $map = [
            'admin' => 'App\\middlewares\\Admin',
            'cajero' => 'App\\middlewares\\Cajero',
            'cliente' => 'App\\middlewares\\Cliente',
        ];
        if (isset($map[strtolower($mw)])) {
            return $map[strtolower($mw)];
        }
        // If it's a FQCN, return as-is
        if (str_contains($mw, '\\')) {
            return $mw;
        }
        // Try default namespace
        return 'App\\Middlewares\\' . $mw;
    }
}
