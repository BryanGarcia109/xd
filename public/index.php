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
$appConfig = require __DIR__ . '/../config/app.php';
date_default_timezone_set($appConfig['timezone']);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener método y URI
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Detectar automáticamente el base path desde la ubicación del script
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$scriptDir = dirname($scriptName);
// Normalizar: si es '/' o '\', entonces basePath es vacío
$basePath = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;

// Normalizar la URI: remover el base path y query string
$requestUri = parse_url($requestUri, PHP_URL_PATH) ?? '/';
// Remover el base path de la URI si está presente
if ($basePath && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
// Normalizar trailing slashes
$requestUri = rtrim($requestUri, '/') ?: '/';

// Manejar rutas raíz y health check
if ($requestUri === '/' || $requestUri === '/index.php') {
    header('Content-Type: application/json');
    echo json_encode([
        'message' => 'API de Gestión de Canchas Sintéticas',
        'version' => '1.0.0',
        'status' => 'ok',
        'endpoints' => [
            'auth' => $basePath . '/api/auth',
            'fields' => $basePath . '/api/fields',
            'bookings' => $basePath . '/api/bookings',
            'payments' => $basePath . '/api/payments',
            'admin' => $basePath . '/api/admin',
            'docs' => $basePath . '/api/docs'
        ]
    ]);
    exit;
}

if ($requestUri === '/health') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'ok',
        'timestamp' => date('Y-m-d H:i:s'),
        'timezone' => date_default_timezone_get()
    ]);
    exit;
}

// Cargar rutas
$routes = require __DIR__ . '/../config/routes.php';

// Crear router y despachar (no necesitamos pasar basePath ya que la URI ya está normalizada)
$router = new App\Core\Router($routes, '');
$router->dispatch($requestMethod, $requestUri);

