<?php

namespace App\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Helpers\SanitizeHelper;
use App\Middlewares\AdminMiddleware;

/**
 * Controlador de Administración
 */
class AdminController extends BaseController
{
    private Booking $bookingModel;
    private Payment $paymentModel;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->paymentModel = new Payment();
    }

    /**
     * Reporte de reservas
     * GET /api/admin/reports/bookings
     */
    public function reportBookings(): void
    {
        $adminMiddleware = new AdminMiddleware();
        if (!$adminMiddleware->check()) {
            return;
        }

        $filters = [];
        
        if (isset($_GET['field_id'])) {
            $filters['field_id'] = SanitizeHelper::int($_GET['field_id']);
        }
        if (isset($_GET['status'])) {
            $filters['status'] = SanitizeHelper::string($_GET['status']);
        }
        if (isset($_GET['from'])) {
            $filters['date_from'] = SanitizeHelper::string($_GET['from']);
        }
        if (isset($_GET['to'])) {
            $filters['date_to'] = SanitizeHelper::string($_GET['to']);
        }

        $bookings = $this->bookingModel->getAll($filters);
        
        // Calcular estadísticas
        $stats = [
            'total' => count($bookings),
            'pending' => 0,
            'confirmed' => 0,
            'cancelled' => 0,
            'completed' => 0,
            'total_revenue' => 0
        ];

        foreach ($bookings as $booking) {
            $stats[$booking['status']]++;
            if ($booking['status'] === 'completed' || $booking['status'] === 'confirmed') {
                $stats['total_revenue'] += floatval($booking['price_total']);
            }
        }

        $this->successResponse([
            'bookings' => $bookings,
            'statistics' => $stats
        ]);
    }

    /**
     * Reporte de ingresos
     * GET /api/admin/reports/revenue
     */
    public function reportRevenue(): void
    {
        $adminMiddleware = new AdminMiddleware();
        if (!$adminMiddleware->check()) {
            return;
        }

        $dateFrom = $_GET['from'] ?? date('Y-m-01'); // Primer día del mes
        $dateTo = $_GET['to'] ?? date('Y-m-d');

        // Obtener pagos completados en el rango de fechas
        $payments = $this->paymentModel->getWithDetails([
            'status' => 'completed',
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);

        // Calcular totales
        $totalRevenue = 0;
        $byMethod = [];
        $byField = [];

        foreach ($payments as $payment) {
            $amount = floatval($payment['amount']);
            $totalRevenue += $amount;

            // Por método de pago
            $method = $payment['method'];
            if (!isset($byMethod[$method])) {
                $byMethod[$method] = 0;
            }
            $byMethod[$method] += $amount;

            // Por cancha
            $fieldId = $payment['field_id'];
            if (!isset($byField[$fieldId])) {
                $byField[$fieldId] = [
                    'field_id' => $fieldId,
                    'field_nombre' => $payment['field_nombre'],
                    'total' => 0
                ];
            }
            $byField[$fieldId]['total'] += $amount;
        }

        $this->successResponse([
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ],
            'total_revenue' => round($totalRevenue, 2),
            'by_method' => $byMethod,
            'by_field' => array_values($byField),
            'payments' => $payments
        ]);
    }
}

