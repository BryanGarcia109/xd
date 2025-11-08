<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Booking;
use App\Helpers\LogHelper;

/**
 * Servicio de Pagos
 * Simula integración con gateway de pago
 */
class PaymentService
{
    private Payment $paymentModel;
    private Booking $bookingModel;

    public function __construct()
    {
        $this->paymentModel = new Payment();
        $this->bookingModel = new Booking();
    }

    /**
     * Procesa un pago (simulación)
     *
     * @param array $data
     * @return array
     */
    public function processPayment(array $data): array
    {
        // Verificar que la reserva existe
        $booking = $this->bookingModel->findById($data['booking_id']);
        if (!$booking) {
            return ['success' => false, 'message' => 'Reserva no encontrada'];
        }

        // Verificar que el monto coincide
        if (abs($data['amount'] - $booking['price_total']) > 0.01) {
            return ['success' => false, 'message' => 'El monto no coincide con la reserva'];
        }

        // Simular procesamiento de pago
        // En producción, aquí se integraría con el gateway real (Stripe, PayPal, etc.)
        $paymentData = [
            'booking_id' => $data['booking_id'],
            'method' => $data['method'],
            'amount' => $data['amount'],
            'status' => 'pending',
            'payment_external_id' => null
        ];

        // Simulación: 90% de éxito
        $simulateSuccess = rand(1, 10) <= 9;

        try {
            $paymentId = $this->paymentModel->create($paymentData);

            if ($simulateSuccess) {
                // Simular respuesta exitosa del gateway
                $externalId = 'MOCK_' . time() . '_' . rand(1000, 9999);
                $this->paymentModel->updateStatus($paymentId, 'completed', $externalId);
                
                // Actualizar estado de la reserva
                $this->bookingModel->updateStatus($data['booking_id'], 'confirmed');

                LogHelper::log('payment_completed', [
                    'payment_id' => $paymentId,
                    'booking_id' => $data['booking_id'],
                    'amount' => $data['amount']
                ]);

                return [
                    'success' => true,
                    'payment_id' => $paymentId,
                    'external_id' => $externalId,
                    'status' => 'completed'
                ];
            } else {
                // Simular fallo
                $this->paymentModel->updateStatus($paymentId, 'failed');

                LogHelper::log('payment_failed', [
                    'payment_id' => $paymentId,
                    'booking_id' => $data['booking_id']
                ]);

                return [
                    'success' => false,
                    'payment_id' => $paymentId,
                    'message' => 'El pago fue rechazado por el gateway'
                ];
            }
        } catch (\Exception $e) {
            LogHelper::log('payment_error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Error al procesar el pago'];
        }
    }

    /**
     * Obtiene el estado de un pago
     *
     * @param int $paymentId
     * @return array|false
     */
    public function getPaymentStatus(int $paymentId)
    {
        return $this->paymentModel->findById($paymentId);
    }
}

