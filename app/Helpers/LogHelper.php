<?php

namespace App\Helpers;

use App\Models\AuditLog;

/**
 * Helper para logging
 */
class LogHelper
{
    /**
     * Registra un log en la base de datos
     *
     * @param string $action
     * @param array $data
     * @param int|null $userId
     * @return void
     */
    public static function log(string $action, array $data = [], ?int $userId = null): void
    {
        try {
            $logModel = new AuditLog();
            $logModel->create([
                'user_id' => $userId ?? self::getCurrentUserId(),
                'action' => $action,
                'data' => $data,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);

            // También escribir en archivo de log
            self::writeToFile($action, $data);
        } catch (\Exception $e) {
            // Si falla, al menos escribir en archivo
            self::writeToFile('log_error', ['error' => $e->getMessage(), 'action' => $action]);
        }
    }

    /**
     * Escribe en archivo de log
     *
     * @param string $action
     * @param array $data
     * @return void
     */
    private static function writeToFile(string $action, array $data): void
    {
        $logFile = __DIR__ . '/../../storage/logs/app.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $message = date('Y-m-d H:i:s') . " [{$action}] " . json_encode($data) . PHP_EOL;
        file_put_contents($logFile, $message, FILE_APPEND);
    }

    /**
     * Obtiene el ID del usuario actual desde la sesión o JWT
     *
     * @return int|null
     */
    private static function getCurrentUserId(): ?int
    {
        // Intentar obtener de sesión
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }

        // Intentar obtener de JWT si está en el contexto
        if (isset($GLOBALS['current_user_id'])) {
            return $GLOBALS['current_user_id'];
        }

        return null;
    }
}

