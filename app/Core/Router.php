<?php

namespace App\Core;

use App\Middlewares\CORSMiddleware;

/**
 * Router Simple
 */
class Router
{
    private array $routes = [];
    private string $basePath = '';

    public function __construct(array $routes, string $basePath = '')
    {
        $this->routes = $routes;
        $this->basePath = $basePath;
    }

    /**
     * Resuelve la ruta y ejecuta el controlador
     *
     * @param string $method
     * @param string $uri
     * @return void
     */
    public function dispatch(string $method, string $uri): void
    {
        // Aplicar CORS
        CORSMiddleware::handle();

        // Normalizar URI
        $uri = $this->normalizeUri($uri);
        $routeKey = "{$method} {$uri}";

        // Buscar ruta exacta
        if (isset($this->routes[$routeKey])) {
            $this->executeRoute($this->routes[$routeKey], []);
            return;
        }

        // Buscar ruta con parámetros
        foreach ($this->routes as $route => $handler) {
            $pattern = $this->routeToPattern($route);
            if (preg_match($pattern, $routeKey, $matches)) {
                array_shift($matches); // Remover el match completo
                $this->executeRoute($handler, $matches);
                return;
            }
        }

        // Ruta no encontrada
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Ruta no encontrada',
            'path' => $uri
        ]);
    }

    /**
     * Normaliza la URI
     *
     * @param string $uri
     * @return string
     */
    private function normalizeUri(string $uri): string
    {
        // Remover query string
        $uri = strtok($uri, '?');
        
        // Remover base path si existe
        if ($this->basePath && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }

        // Normalizar
        $uri = rtrim($uri, '/') ?: '/';
        
        return $uri;
    }

    /**
     * Convierte una ruta con parámetros a patrón regex
     *
     * @param string $route
     * @return string
     */
    private function routeToPattern(string $route): string
    {
        // Separar método y ruta
        $parts = explode(' ', $route, 2);
        $method = $parts[0];
        $path = $parts[1] ?? '';
        
        // Escapar caracteres especiales excepto {}
        $pattern = preg_quote($path, '/');
        // Reemplazar {id} o {param} por grupo de captura para números
        $pattern = preg_replace('/\\\{(\w+)\\\}/', '(\d+)', $pattern);
        
        // Reconstruir con método
        return '/^' . preg_quote($method, '/') . ' ' . $pattern . '$/';
    }

    /**
     * Ejecuta el handler de la ruta
     *
     * @param array $handler
     * @param array $params
     * @return void
     */
    private function executeRoute(array $handler, array $params): void
    {
        [$controllerClass, $method] = $handler;

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Controlador no encontrado'
            ]);
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Método no encontrado'
            ]);
            return;
        }

        // Ejecutar con parámetros
        if (empty($params)) {
            $controller->$method();
        } else {
            $controller->$method(...$params);
        }
    }
}

