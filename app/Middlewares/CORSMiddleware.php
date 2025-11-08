<?php

namespace App\Middlewares;

/**
 * Middleware para CORS
 */
class CORSMiddleware
{
    /**
     * Configura los headers CORS
     *
     * @return void
     */
    public static function handle(): void
    {
        $allowedOrigins = explode(',', getenv('CORS_ALLOWED_ORIGINS') ?: 'http://localhost:3000');
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: {$origin}");
        } else {
            header("Access-Control-Allow-Origin: *");
        }

        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 3600");

        // Manejar preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}

