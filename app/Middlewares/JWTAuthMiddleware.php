<?php

namespace App\Middlewares;

use App\Services\AuthService;
use App\Helpers\ResponseHelper;

/**
 * Middleware de Autenticación JWT
 */
class JWTAuthMiddleware
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Verifica si el usuario está autenticado mediante JWT
     *
     * @return array|false
     */
    public function authenticate()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader) {
            ResponseHelper::error('Token de autenticación no proporcionado', 401);
            return false;
        }

        // Extraer token del header "Bearer {token}"
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        } else {
            ResponseHelper::error('Formato de token inválido', 401);
            return false;
        }

        $decoded = $this->authService->validateToken($token);
        if (!$decoded) {
            ResponseHelper::error('Token inválido o expirado', 401);
            return false;
        }

        // Guardar información del usuario en contexto global
        $GLOBALS['current_user'] = $decoded;
        $GLOBALS['current_user_id'] = $decoded['user_id'];

        return $decoded;
    }

    /**
     * Obtiene el usuario actual autenticado
     *
     * @return array|null
     */
    public static function getCurrentUser(): ?array
    {
        return $GLOBALS['current_user'] ?? null;
    }

    /**
     * Obtiene el ID del usuario actual
     *
     * @return int|null
     */
    public static function getCurrentUserId(): ?int
    {
        return $GLOBALS['current_user_id'] ?? null;
    }
}

