<?php

namespace App\Middlewares;

use App\Middlewares\JWTAuthMiddleware;
use App\Helpers\ResponseHelper;

/**
 * Middleware para verificar permisos de administrador
 */
class AdminMiddleware
{
    /**
     * Verifica si el usuario es administrador
     *
     * @return bool
     */
    public function check(): bool
    {
        // Primero verificar autenticación
        $jwtMiddleware = new JWTAuthMiddleware();
        $user = $jwtMiddleware->authenticate();

        if (!$user) {
            return false;
        }

        // Verificar rol de administrador
        if ($user['role'] !== 'admin') {
            ResponseHelper::error('Acceso denegado. Se requieren permisos de administrador', 403);
            return false;
        }

        return true;
    }
}

