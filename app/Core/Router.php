<?php
// app/Core/Router.php

class Router {
    private array $routes = [];

    // Adaugă o rută de tip GET. Acceptă string (ex: 'Controller@metoda') sau funcție anonimă (callable)
    public function get(string $uri, string|callable $action) {
        $this->routes[] = ['method' => 'GET', 'uri' => $uri, 'action' => $action];
    }

    // Adaugă o rută de tip POST
    public function post(string $uri, string|callable $action) {
        $this->routes[] = ['method' => 'POST', 'uri' => $uri, 'action' => $action];
    }

    // Găsește ruta corectă pe baza URL-ului
    public function dispatch(string $requestUri, string $requestMethod) {
        // Curățăm URL-ul
        $uri = strtok($requestUri, '?');
        $uri = str_replace('/public', '', $uri); 
        $uri = rtrim($uri, '/') ?: '/'; 

        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === strtoupper($requestMethod)) {
                
                // 1. Dacă acțiunea este o funcție anonimă (Closure / Callable)
                if (is_callable($route['action'])) {
                    return call_user_func($route['action']);
                }
                
                // 2. Dacă acțiunea este un string (ex: 'HomeController@index')
                if (is_string($route['action'])) {
                    [$controller, $method] = explode('@', $route['action']);
                    
                    require_once BASE_PATH . "Controllers/{$controller}.php";
                    
                    $controllerInstance = new $controller();
                    return $controllerInstance->$method();
                }
            }
        }

        // Dacă nu am găsit ruta, returnăm eroarea 404
        http_response_code(404);
        echo "<h1>404 - Pagina nu a fost găsită</h1>";
    }
}