<?php
class Router {
    private $routes = [];
    private $groupPrefix = '';
    private $groupMiddleware = [];

    public function get($path, $handler, $middleware = []) {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post($path, $handler, $middleware = []) {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function any($path, $handler, $middleware = []) {
        $this->addRoute('GET', $path, $handler, $middleware);
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function group($prefix, $middleware, callable $callback) {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix = $previousPrefix . $prefix;
        $this->groupMiddleware = array_merge($previousMiddleware, (array)$middleware);

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    private function addRoute($method, $path, $handler, $middleware) {
        $fullPath = $this->groupPrefix . '/' . ltrim($path, '/');
        $fullPath = '/' . trim($fullPath, '/');

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => array_merge($this->groupMiddleware, (array)$middleware),
        ];
    }

    public function dispatch($method, $url) {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $basePath = str_replace(basename($scriptName), '', $scriptName);
        $basePath = rtrim($basePath, '/');

        if ($basePath && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        $path = '/' . trim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $pattern = $this->buildPattern($route['path']);

            if (preg_match($pattern, $path, $matches)) {
                foreach ($route['middleware'] as $mw) {
                    $middlewareClass = "Middleware\\{$mw}";
                    if (class_exists($middlewareClass)) {
                        $result = (new $middlewareClass())->handle();
                        if ($result === false) return;
                    }
                }

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return $this->callHandler($route['handler'], $params);
            }
        }

        http_response_code(404);
        require VIEWS_PATH . '/errors/404.php';
    }

    private function buildPattern($path) {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '/?$#';
    }

    private function callHandler($handler, $params) {
        if (is_array($handler)) {
            $controllerClass = "Controllers\\{$handler[0]}";
            $method = $handler[1];
            $controller = new $controllerClass();
            return call_user_func_array([$controller, $method], $params);
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$controllerClass, $method] = explode('@', $handler);
            $controllerClass = "Controllers\\{$controllerClass}";
            $controller = new $controllerClass();
            return call_user_func_array([$controller, $method], $params);
        }

        return call_user_func_array($handler, $params);
    }

    public function getRoutes() {
        return $this->routes;
    }
}
