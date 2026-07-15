<?php

class Router
{
    private array $routes = [];

    public function get(string $path, string $controller, string $method): void
    {
        $this->routes[] = ['GET', $path, $controller, $method];
    }

    public function post(string $path, string $controller, string $method): void
    {
        $this->routes[] = ['POST', $path, $controller, $method];
    }

    public function any(string $path, string $controller, string $method): void
    {
        $this->routes[] = ['ANY', $path, $controller, $method];
    }

    public function dispatch(string $url, string $httpMethod): void
    {
        $url = trim($url, '/');

        foreach ($this->routes as [$routeMethod, $path, $controller, $action]) {
            // Build regex from path params, e.g. {id}
            $pattern = preg_replace('/\{[a-z_]+\}/', '([^/]+)', $path);
            $pattern = '@^' . $pattern . '$@';

            if (!preg_match($pattern, $url, $matches)) continue;

            if ($routeMethod !== 'ANY' && $routeMethod !== strtoupper($httpMethod)) {
                http_response_code(405);
                die('Method Not Allowed');
            }

            array_shift($matches); // Remove full match

            if (!class_exists($controller)) {
                http_response_code(500);
                die("Controller {$controller} not found.");
            }

            $obj = new $controller();

            if (!method_exists($obj, $action)) {
                http_response_code(500);
                die("Action {$action} not found in {$controller}.");
            }

            call_user_func_array([$obj, $action], $matches);
            return;
        }

        // No route matched
        http_response_code(404);
        require APP_PATH . '/views/errors/404.php';
    }
}
