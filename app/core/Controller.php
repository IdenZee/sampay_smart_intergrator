<?php

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'main'): void
    {
        // Flatten $data into local variables for the view
        extract($data);

        $viewFile = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(404);
            die("View not found: {$view}");
        }

        if ($layout) {
            $layoutFile = APP_PATH . '/views/layouts/' . $layout . '.php';
            if (!file_exists($layoutFile)) {
                die("Layout not found: {$layout}");
            }
            // $content is available inside the layout
            ob_start();
            require $viewFile;
            $content = ob_get_clean();
            require $layoutFile;
        } else {
            require $viewFile;
        }
    }

    protected function redirect(string $path, int $code = 302): void
    {
        header('Location: ' . APP_URL . '/' . ltrim($path, '/'), true, $code);
        exit;
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function abort(int $code = 404, string $message = 'Not Found'): void
    {
        http_response_code($code);
        die($message);
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function post(string $key, mixed $default = null): mixed
    {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
    }
}
