<?php

namespace App\Middlewares;

use App\Helpers\ResponseHelper;

/**
 * Middleware de Rate Limiting simple
 * Implementación básica usando archivos
 */
class RateLimitMiddleware
{
    private string $storagePath;
    private int $maxRequests;
    private int $windowSeconds;

    public function __construct(int $maxRequests = 100, int $windowSeconds = 3600)
    {
        $this->storagePath = __DIR__ . '/../../storage/cache/';
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;

        // Crear directorio si no existe
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Verifica si la solicitud excede el límite
     *
     * @param string $identifier Identificador único (IP, user_id, etc.)
     * @return bool
     */
    public function check(string $identifier): bool
    {
        // Verificar si está habilitado
        if (!filter_var(getenv('RATE_LIMIT_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        $file = $this->storagePath . md5($identifier) . '.json';
        $now = time();

        // Leer datos existentes
        $data = [];
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $data = json_decode($content, true) ?? [];
        }

        // Limpiar requests antiguos
        if (isset($data['requests'])) {
            $data['requests'] = array_filter($data['requests'], function($timestamp) use ($now) {
                return ($now - $timestamp) < $this->windowSeconds;
            });
            $data['requests'] = array_values($data['requests']);
        } else {
            $data['requests'] = [];
        }

        // Verificar límite
        if (count($data['requests']) >= $this->maxRequests) {
            ResponseHelper::error('Demasiadas solicitudes. Por favor, intente más tarde.', 429);
            return false;
        }

        // Agregar nueva solicitud
        $data['requests'][] = $now;
        $data['last_request'] = $now;

        // Guardar
        file_put_contents($file, json_encode($data));

        return true;
    }

    /**
     * Limpia archivos de rate limit antiguos
     *
     * @return void
     */
    public function cleanup(): void
    {
        $files = glob($this->storagePath . '*.json');
        $now = time();

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $data = json_decode($content, true);

            if ($data && isset($data['last_request'])) {
                // Eliminar archivos con más de 24 horas sin uso
                if (($now - $data['last_request']) > 86400) {
                    unlink($file);
                }
            }
        }
    }
}

