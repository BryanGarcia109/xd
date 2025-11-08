<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Field;
use App\Models\FieldSchedule;
use App\Helpers\LogHelper;

/**
 * Servicio de Reservas
 * Contiene la lógica de negocio para reservas
 */
class BookingService
{
    private Booking $bookingModel;
    private Field $fieldModel;
    private FieldSchedule $scheduleModel;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->fieldModel = new Field();
        $this->scheduleModel = new FieldSchedule();
    }

    /**
     * Calcula el precio total de una reserva
     *
     * @param int $fieldId
     * @param int $durationMinutes
     * @return float
     */
    public function calculatePrice(int $fieldId, int $durationMinutes): float
    {
        $field = $this->fieldModel->findById($fieldId);
        if (!$field) {
            return 0;
        }

        $hours = $durationMinutes / 60;
        return round($field['price_per_hour'] * $hours, 2);
    }

    /**
     * Verifica disponibilidad de una cancha
     *
     * @param int $fieldId
     * @param string $date
     * @return array
     */
    public function getAvailability(int $fieldId, string $date): array
    {
        $field = $this->fieldModel->findById($fieldId);
        if (!$field) {
            return [];
        }

        // Obtener día de la semana (0=Domingo, 6=Sábado)
        $dayOfWeek = (int) date('w', strtotime($date));

        // Obtener horarios para ese día
        $schedules = $this->scheduleModel->getScheduleForDay($fieldId, $dayOfWeek, $date);

        if (empty($schedules)) {
            return [];
        }

        // Obtener reservas existentes para esa fecha
        $bookings = $this->bookingModel->getByFieldAndDate($fieldId, $date);

        // Generar franjas disponibles
        $availableSlots = [];
        foreach ($schedules as $schedule) {
            $start = strtotime($schedule['start_time']);
            $end = strtotime($schedule['end_time']);
            $duration = $schedule['duration_minutes'] * 60;

            $current = $start;
            while ($current + $duration <= $end) {
                $slotStart = date('H:i:s', $current);
                $slotEnd = date('H:i:s', $current + $duration);

                // Verificar si hay conflicto con reservas existentes
                $isAvailable = true;
                foreach ($bookings as $booking) {
                    $bookingStart = strtotime($booking['start_time']);
                    $bookingEnd = $bookingStart + ($booking['duration_minutes'] * 60);
                    
                    if (!($current + $duration <= $bookingStart || $current >= $bookingEnd)) {
                        $isAvailable = false;
                        break;
                    }
                }

                if ($isAvailable) {
                    $availableSlots[] = [
                        'start_time' => $slotStart,
                        'end_time' => $slotEnd,
                        'duration_minutes' => $schedule['duration_minutes']
                    ];
                }

                $current += $duration;
            }
        }

        return $availableSlots;
    }

    /**
     * Crea una nueva reserva con validaciones
     *
     * @param array $data
     * @return array
     */
    public function createBooking(array $data): array
    {
        // Verificar que la cancha existe y está activa
        $field = $this->fieldModel->findById($data['field_id']);
        if (!$field || $field['status'] !== 'active') {
            return ['success' => false, 'message' => 'Cancha no disponible'];
        }

        // Verificar disponibilidad
        $availability = $this->getAvailability($data['field_id'], $data['date']);
        $slotAvailable = false;
        foreach ($availability as $slot) {
            if ($slot['start_time'] === $data['start_time']) {
                $slotAvailable = true;
                break;
            }
        }

        if (!$slotAvailable) {
            return ['success' => false, 'message' => 'Horario no disponible'];
        }

        // Verificar conflicto (doble verificación)
        $conflict = $this->bookingModel->findConflict(
            $data['field_id'],
            $data['date'],
            $data['start_time'],
            $data['duration_minutes']
        );

        if ($conflict) {
            return ['success' => false, 'message' => 'Ya existe una reserva en ese horario'];
        }

        // Calcular precio
        $priceTotal = $this->calculatePrice($data['field_id'], $data['duration_minutes']);
        $data['price_total'] = $priceTotal;
        $data['status'] = 'pending';

        // Crear reserva
        try {
            $bookingId = $this->bookingModel->create($data);
            
            LogHelper::log('booking_created', [
                'booking_id' => $bookingId,
                'user_id' => $data['user_id'],
                'field_id' => $data['field_id']
            ]);

            return [
                'success' => true,
                'booking_id' => $bookingId,
                'price_total' => $priceTotal
            ];
        } catch (\Exception $e) {
            LogHelper::log('booking_error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Error al crear la reserva'];
        }
    }

    /**
     * Verifica si una reserva puede ser cancelada
     *
     * @param int $bookingId
     * @return array
     */
    public function canCancelBooking(int $bookingId): array
    {
        $booking = $this->bookingModel->findById($bookingId);
        if (!$booking) {
            return ['can_cancel' => false, 'message' => 'Reserva no encontrada'];
        }

        if ($booking['status'] === 'cancelled') {
            return ['can_cancel' => false, 'message' => 'La reserva ya está cancelada'];
        }

        if ($booking['status'] === 'completed') {
            return ['can_cancel' => false, 'message' => 'No se puede cancelar una reserva completada'];
        }

        // Verificar política de 24 horas
        $bookingDateTime = strtotime("{$booking['date']} {$booking['start_time']}");
        $now = time();
        $hoursUntilBooking = ($bookingDateTime - $now) / 3600;

        if ($hoursUntilBooking < 24) {
            return [
                'can_cancel' => false,
                'message' => 'No se puede cancelar con menos de 24 horas de anticipación'
            ];
        }

        return ['can_cancel' => true];
    }
}

