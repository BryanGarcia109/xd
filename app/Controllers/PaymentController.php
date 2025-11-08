<?php

namespace App\Controllers;

use App\Models\Payment;
use App\Services\PaymentService;
use App\Helpers\SanitizeHelper;
use App\Helpers\ValidationHelper;
use App\Middlewares\JWTAuthMiddleware;

/**
 * Controlador de Pagos
 */
class PaymentController extends BaseController
{
    private Payment $paymentModel;
    private PaymentService $paymentService;

    public function __construct()
    {
        $this->paymentModel = new Payment();
        $this->paymentService = new PaymentService();
    }

    /**
     * Procesar un pago
     * POST /api/payments
     */
    public function process(): void
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
        if (!ValidationHelper::required($input, 'booking_id')) {
            $errors[] = 'El ID de la reserva es requerido';
        }
        if (!ValidationHelper::required($input, 'method')) {
            $errors[] = 'El método de pago es requerido';
        }
        if (!ValidationHelper::required($input, 'amount')) {
            $errors[] = 'El monto es requerido';
        } elseif (!is_numeric($input['amount']) || $input['amount'] <= 0) {
            $errors[] = 'El monto debe ser un número positivo';
        }

        if (!empty($errors)) {
            $this->errorResponse(implode(', ', $errors), 400);
            return;
        }

        $paymentData = [
            'booking_id' => SanitizeHelper::int($input['booking_id']),
            'method' => SanitizeHelper::string($input['method']),
            'amount' => SanitizeHelper::float($input['amount'])
        ];

        $result = $this->paymentService->processPayment($paymentData);
        
        if (!$result['success']) {
            $this->errorResponse($result['message'], 400);
            return;
        }

        $payment = $this->paymentModel->findById($result['payment_id']);
        $this->successResponse($payment, 'Pago procesado correctamente');
    }

    /**
     * Obtener estado de un pago
     * GET /api/payments/{id}
     */
    public function show(int $id): void
    {
        $user = JWTAuthMiddleware::getCurrentUser();
        if (!$user) {
            $this->errorResponse('No autenticado', 401);
            return;
        }

        $payment = $this->paymentModel->findById($id);
        if (!$payment) {
            $this->errorResponse('Pago no encontrado', 404);
            return;
        }

        // Verificar que el usuario tiene acceso a este pago
        if ($user['role'] !== 'admin' && $payment['user_id'] != $user['user_id']) {
            $this->errorResponse('No tienes permiso para ver este pago', 403);
            return;
        }

        $this->successResponse($payment);
    }

    /**
     * Obtener pagos de una reserva
     * GET /api/payments/booking/{booking_id}
     */
    public function getByBooking(int $bookingId): void
    {
        $user = JWTAuthMiddleware::getCurrentUser();
        if (!$user) {
            $this->errorResponse('No autenticado', 401);
            return;
        }

        $payments = $this->paymentModel->getByBookingId($bookingId);
        $this->successResponse($payments);
    }
}

