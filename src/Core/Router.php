<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, callable|array $handler, array $middleware): void
    {
        $this->routes[$method][$path] = [
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        $route = $this->routes[$method][$path] ?? null;

        if (!$route) {
            App::abort(404, 'Page not found.');
        }

        foreach ($route['middleware'] as $middleware) {
            if (is_array($middleware)) {
                [$class, $argument] = $middleware;
                (new $class())->handle($argument);
                continue;
            }

            (new $middleware())->handle();
        }

        $handler = $route['handler'];

        if (is_array($handler)) {
            [$class, $methodName] = $handler;
            (new $class())->{$methodName}();
            return;
        }

        $handler();
    }
}
