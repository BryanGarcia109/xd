<?php

namespace App\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use App\Helpers\SanitizeHelper;
use App\Helpers\ValidationHelper;
use App\Middlewares\JWTAuthMiddleware;

/**
 * Controlador de Reservas
 */
class BookingController extends BaseController
{
    private Booking $bookingModel;
    private BookingService $bookingService;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->bookingService = new BookingService();
    }

    /**
     * Listar reservas
     * GET /api/bookings
     */
    public function index(): void
    {
        $user = JWTAuthMiddleware::getCurrentUser();
        if (!$user) {
            $this->errorResponse('No autenticado', 401);
            return;
        }

        $filters = [];

        // Si no es admin, solo puede ver sus propias reservas
        if ($user['role'] !== 'admin') {
            $filters['user_id'] = $user['user_id'];
        } elseif (isset($_GET['user_id'])) {
            $filters['user_id'] = SanitizeHelper::int($_GET['user_id']);
        }

        if (isset($_GET['field_id'])) {
            $filters['field_id'] = SanitizeHelper::int($_GET['field_id']);
        }
        if (isset($_GET['status'])) {
            $filters['status'] = SanitizeHelper::string($_GET['status']);
        }
        if (isset($_GET['date_from'])) {
            $filters['date_from'] = SanitizeHelper::string($_GET['date_from']);
        }
        if (isset($_GET['date_to'])) {
            $filters['date_to'] = SanitizeHelper::string($_GET['date_to']);
        }

        $bookings = $this->bookingModel->getAll($filters);
        $this->successResponse($bookings);
    }

    /**
     * Obtener una reserva por ID
     * GET /api/bookings/{id}
     */
    public function show(int $id): void
    {
        $user = JWTAuthMiddleware::getCurrentUser();
        if (!$user) {
            $this->errorResponse('No autenticado', 401);
            return;
        }

        $booking = $this->bookingModel->findById($id);
        if (!$booking) {
            $this->errorResponse('Reserva no encontrada', 404);
            return;
        }

        // Verificar que el usuario tiene acceso a esta reserva
        if ($user['role'] !== 'admin' && $booking['user_id'] != $user['user_id']) {
            $this->errorResponse('No tienes permiso para ver esta reserva', 403);
            return;
        }

        $this->successResponse($booking);
    }

    /**
     * Crear una reserva
     * POST /api/bookings
     */
    public function create(): void
    {
        $user = JWTAuthMiddleware::getCurrentUser();
        if (!$user) {
            $this->errorResponse('No autenticado', 401);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $this->errorResponse('Datos inválidos', 400);
            return;
        }

        // Validaciones
        $errors = [];
        if (!ValidationHelper::required($input, 'field_id')) {
            $errors[] = 'El ID de la cancha es requerido';
        }
        if (!ValidationHelper::required($input, 'date')) {
            $errors[] = 'La fecha es requerida';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['date'])) {
            $errors[] = 'Formato de fecha inválido. Use YYYY-MM-DD';
        }
        if (!ValidationHelper::required($input, 'start_time')) {
            $errors[] = 'La hora de inicio es requerida';
        } elseif (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $input['start_time'])) {
            $errors[] = 'Formato de hora inválido. Use HH:MM o HH:MM:SS';
        }
        if (!ValidationHelper::required($input, 'duration_minutes')) {
            $errors[] = 'La duración es requerida';
        } elseif (!is_numeric($input['duration_minutes']) || $input['duration_minutes'] < 30) {
            $errors[] = 'La duración mínima es 30 minutos';
        } elseif ($input['duration_minutes'] > 480) {
            $errors[] = 'La duración máxima es 8 horas';
        }

        if (!empty($errors)) {
            $this->errorResponse(implode(', ', $errors), 400);
            return;
        }

        // Verificar que la fecha no sea en el pasado
        $bookingDate = strtotime($input['date']);
        $today = strtotime(date('Y-m-d'));
        if ($bookingDate < $today) {
            $this->errorResponse('No se pueden hacer reservas en fechas pasadas', 400);
            return;
        }

        $bookingData = [
            'user_id' => $user['user_id'],
            'field_id' => SanitizeHelper::int($input['field_id']),
            'date' => $input['date'],
            'start_time' => $input['start_time'],
            'duration_minutes' => SanitizeHelper::int($input['duration_minutes'])
        ];

        $result = $this->bookingService->createBooking($bookingData);
        
        if (!$result['success']) {
            $this->errorResponse($result['message'], 400);
            return;
        }

        $booking = $this->bookingModel->findById($result['booking_id']);
        $this->successResponse($booking, 'Reserva creada correctamente', 201);
    }

    /**
     * Cancelar una reserva
     * PUT /api/bookings/{id}/cancel
     */
    public function cancel(int $id): void
    {
        $user = JWTAuthMiddleware::getCurrentUser();
        if (!$user) {
            $this->errorResponse('No autenticado', 401);
            return;
        }

        $booking = $this->bookingModel->findById($id);
        if (!$booking) {
            $this->errorResponse('Reserva no encontrada', 404);
            return;
        }

        // Verificar permisos
        if ($user['role'] !== 'admin' && $booking['user_id'] != $user['user_id']) {
            $this->errorResponse('No tienes permiso para cancelar esta reserva', 403);
            return;
        }

        // Verificar si puede cancelar
        $canCancel = $this->bookingService->canCancelBooking($id);
        if (!$canCancel['can_cancel']) {
            $this->errorResponse($canCancel['message'], 400);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $reason = isset($input['reason']) ? SanitizeHelper::string($input['reason']) : 'Cancelación por usuario';

        $this->bookingModel->updateStatus($id, 'cancelled', $reason);
        
        $booking = $this->bookingModel->findById($id);
        $this->successResponse($booking, 'Reserva cancelada correctamente');
    }
}

