<?php

/**
 * Front Controller
 * Punto de entrada principal de la aplicación
 */

// Cargar autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno (si existe el archivo .env)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Configurar zona horaria
date_default_timezone_set('America/Lima');

// Router simple
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Eliminar query string de la URI
$requestUri = strtok($requestUri, '?');

// Eliminar la base URL si existe
$baseUrl = getenv('BASE_URL') ?: 'http://localhost:8000';
$basePath = parse_url($baseUrl, PHP_URL_PATH) ?? '';
if ($basePath && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Normalizar la URI
$requestUri = rtrim($requestUri, '/') ?: '/';

// Definir rutas (esto se puede mover a un archivo routes.php más adelante)
$routes = [
    'GET /' => function() {
        header('Content-Type: application/json');
        echo json_encode([
            'message' => 'API de Gestión de Canchas Sintéticas',
            'version' => '1.0.0',
            'status' => 'ok'
        ]);
    },
    'GET /health' => function() {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    },
];

// Buscar la ruta
$routeKey = "$requestMethod $requestUri";
$routeFound = false;

foreach ($routes as $route => $handler) {
    // Comparación simple (se puede mejorar con expresiones regulares)
    if ($route === $routeKey) {
        $routeFound = true;
        $handler();
        break;
    }
}

// Si no se encuentra la ruta, retornar 404
if (!$routeFound) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Ruta no encontrada',
        'path' => $requestUri
    ]);
}

