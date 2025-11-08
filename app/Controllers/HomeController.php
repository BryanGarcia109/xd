<?php

namespace App\Controllers;

/**
 * Controlador de Ejemplo - Home
 * Muestra cómo extender BaseController
 */
class HomeController extends BaseController
{
    /**
     * Método de ejemplo
     *
     * @return void
     */
    public function index(): void
    {
        $this->successResponse([
            'message' => 'Bienvenido a la API de Gestión de Canchas Sintéticas',
            'version' => '1.0.0'
        ]);
    }
}

