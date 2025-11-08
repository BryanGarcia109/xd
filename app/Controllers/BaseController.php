<?php

namespace App\Controllers;

/**
 * Controlador Base
 * Proporciona métodos comunes para todos los controladores
 */
class BaseController
{
    /**
     * Envía una respuesta JSON
     *
     * @param mixed $data Datos a enviar
     * @param int $statusCode Código de estado HTTP
     * @return void
     */
    protected function jsonResponse($data, int $statusCode = 200): void
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
    protected function errorResponse(string $message, int $statusCode = 400): void
    {
        $this->jsonResponse([
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
    protected function successResponse($data = null, string $message = '', int $statusCode = 200): void
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

        $this->jsonResponse($response, $statusCode);
    }
}

