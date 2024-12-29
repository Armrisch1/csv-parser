<?php

namespace src\core;

use src\services\ResponseService;

class Router
{
    private array $routes = [];

    /**
     * @param $path
     * @param $callback
     * @return void
     */
    public function get($path, $callback): void
    {
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * @param $path
     * @param $callback
     * @return void
     */
    public function post($path, $callback): void
    {
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * @return void
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = strtok($_SERVER['REQUEST_URI'], '?');

        if (isset($this->routes[$method][$path])) {
            $callback = $this->routes[$method][$path];

            if (is_callable($callback)) {
                call_user_func($callback);
            } elseif (is_array($callback)) {
                [$controller, $method] = $callback;

                (new $controller())->$method();
            }
        } else {
            ResponseService::sendErrorResponse(404, 'Page Not Found');
        }
    }
}
