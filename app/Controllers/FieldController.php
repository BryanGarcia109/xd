<?php

namespace App\Controllers;

use App\Models\Field;
use App\Services\BookingService;
use App\Helpers\SanitizeHelper;
use App\Helpers\ValidationHelper;
use App\Middlewares\JWTAuthMiddleware;
use App\Middlewares\AdminMiddleware;

/**
 * Controlador de Canchas
 */
class FieldController extends BaseController
{
    private Field $fieldModel;
    private BookingService $bookingService;

    public function __construct()
    {
        $this->fieldModel = new Field();
        $this->bookingService = new BookingService();
    }

    /**
     * Listar canchas
     * GET /api/fields
     */
    public function index(): void
    {
        $filters = [];
        
        if (isset($_GET['status'])) {
            $filters['status'] = SanitizeHelper::string($_GET['status']);
        }
        if (isset($_GET['ubicacion'])) {
            $filters['ubicacion'] = SanitizeHelper::string($_GET['ubicacion']);
        }
        if (isset($_GET['tipo'])) {
            $filters['tipo'] = SanitizeHelper::string($_GET['tipo']);
        }
        if (isset($_GET['min_price'])) {
            $filters['min_price'] = SanitizeHelper::float($_GET['min_price']);
        }
        if (isset($_GET['max_price'])) {
            $filters['max_price'] = SanitizeHelper::float($_GET['max_price']);
        }

        $fields = $this->fieldModel->getAll($filters);
        $this->successResponse($fields);
    }

    /**
     * Obtener una cancha por ID
     * GET /api/fields/{id}
     */
    public function show(int $id): void
    {
        $field = $this->fieldModel->findById($id);
        if (!$field) {
            $this->errorResponse('Cancha no encontrada', 404);
            return;
        }

        $this->successResponse($field);
    }

    /**
     * Crear una cancha (Admin)
     * POST /api/fields
     */
    public function create(): void
    {
        $adminMiddleware = new AdminMiddleware();
        if (!$adminMiddleware->check()) {
            return; // Ya se envió la respuesta de error
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $this->errorResponse('Datos inválidos', 400);
            return;
        }

        // Validaciones
        $errors = [];
        if (!ValidationHelper::required($input, 'nombre')) {
            $errors[] = 'El nombre es requerido';
        }
        if (!ValidationHelper::required($input, 'ubicacion')) {
            $errors[] = 'La ubicación es requerida';
        }
        if (!ValidationHelper::required($input, 'price_per_hour')) {
            $errors[] = 'El precio por hora es requerido';
        } elseif (!is_numeric($input['price_per_hour']) || $input['price_per_hour'] < 0) {
            $errors[] = 'El precio por hora debe ser un número positivo';
        }

        if (!empty($errors)) {
            $this->errorResponse(implode(', ', $errors), 400);
            return;
        }

        try {
            $fieldId = $this->fieldModel->create([
                'nombre' => SanitizeHelper::string($input['nombre']),
                'descripcion' => isset($input['descripcion']) ? SanitizeHelper::string($input['descripcion']) : null,
                'ubicacion' => SanitizeHelper::string($input['ubicacion']),
                'tipo' => SanitizeHelper::string($input['tipo'] ?? 'sintética'),
                'dimensiones' => isset($input['dimensiones']) ? SanitizeHelper::string($input['dimensiones']) : null,
                'price_per_hour' => SanitizeHelper::float($input['price_per_hour']),
                'photo_url' => isset($input['photo_url']) ? SanitizeHelper::string($input['photo_url']) : null,
                'status' => $input['status'] ?? 'active'
            ]);

            $this->successResponse(['field_id' => $fieldId], 'Cancha creada correctamente', 201);
        } catch (\Exception $e) {
            $this->errorResponse('Error al crear la cancha', 500);
        }
    }

    /**
     * Actualizar una cancha (Admin)
     * PUT /api/fields/{id}
     */
    public function update(int $id): void
    {
        $adminMiddleware = new AdminMiddleware();
        if (!$adminMiddleware->check()) {
            return;
        }

        $field = $this->fieldModel->findById($id);
        if (!$field) {
            $this->errorResponse('Cancha no encontrada', 404);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $this->errorResponse('Datos inválidos', 400);
            return;
        }

        $updateData = [];
        if (isset($input['nombre'])) {
            $updateData['nombre'] = SanitizeHelper::string($input['nombre']);
        }
        if (isset($input['descripcion'])) {
            $updateData['descripcion'] = SanitizeHelper::string($input['descripcion']);
        }
        if (isset($input['ubicacion'])) {
            $updateData['ubicacion'] = SanitizeHelper::string($input['ubicacion']);
        }
        if (isset($input['tipo'])) {
            $updateData['tipo'] = SanitizeHelper::string($input['tipo']);
        }
        if (isset($input['dimensiones'])) {
            $updateData['dimensiones'] = SanitizeHelper::string($input['dimensiones']);
        }
        if (isset($input['price_per_hour'])) {
            $updateData['price_per_hour'] = SanitizeHelper::float($input['price_per_hour']);
        }
        if (isset($input['photo_url'])) {
            $updateData['photo_url'] = SanitizeHelper::string($input['photo_url']);
        }
        if (isset($input['status'])) {
            $updateData['status'] = SanitizeHelper::string($input['status']);
        }

        if (empty($updateData)) {
            $this->errorResponse('No hay datos para actualizar', 400);
            return;
        }

        $this->fieldModel->update($id, $updateData);
        $this->successResponse([], 'Cancha actualizada correctamente');
    }

    /**
     * Eliminar una cancha (Admin)
     * DELETE /api/fields/{id}
     */
    public function delete(int $id): void
    {
        $adminMiddleware = new AdminMiddleware();
        if (!$adminMiddleware->check()) {
            return;
        }

        $field = $this->fieldModel->findById($id);
        if (!$field) {
            $this->errorResponse('Cancha no encontrada', 404);
            return;
        }

        $this->fieldModel->delete($id);
        $this->successResponse([], 'Cancha eliminada correctamente');
    }

    /**
     * Obtener disponibilidad de una cancha
     * GET /api/fields/{id}/availability
     */
    public function availability(int $id): void
    {
        $field = $this->fieldModel->findById($id);
        if (!$field) {
            $this->errorResponse('Cancha no encontrada', 404);
            return;
        }

        if (!isset($_GET['date'])) {
            $this->errorResponse('El parámetro date es requerido', 400);
            return;
        }

        $date = SanitizeHelper::string($_GET['date']);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->errorResponse('Formato de fecha inválido. Use YYYY-MM-DD', 400);
            return;
        }

        $availability = $this->bookingService->getAvailability($id, $date);
        $this->successResponse([
            'field_id' => $id,
            'date' => $date,
            'available_slots' => $availability
        ]);
    }
}

