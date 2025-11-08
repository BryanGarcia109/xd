<?php

namespace App\Helpers;

/**
 * Helper para respuestas HTTP
 */
class ResponseHelper
{
    /**
     * Envía una respuesta JSON
     *
     * @param mixed $data Datos a enviar
     * @param int $statusCode Código de estado HTTP
     * @return void
     */
    public static function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Envía una respuesta de error
     *
     * @param string $message Mensaje de error
     * @param int $statusCode Código de estado HTTP
     * @return void
     */
    public static function error(string $message, int $statusCode = 400): void
    {
        self::json([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }

    /**
     * Envía una respuesta de éxito
     *
     * @param mixed $data Datos a enviar
     * @param string $message Mensaje de éxito
     * @param int $statusCode Código de estado HTTP
     * @return void
     */
    public static function success($data = null, string $message = '', int $statusCode = 200): void
    {
        $response = [
            'success' => true
        ];

        if (!empty($message)) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        self::json($response, $statusCode);
    }
}

