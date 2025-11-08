<?php

namespace App\Models;

/**
 * Modelo de Reserva
 */
class Booking extends BaseModel
{
    protected string $table = 'bookings';

    /**
     * Crea una nueva reserva
     *
     * @param array $data
     * @return string ID de la reserva creada
     */
    public function create(array $data): string
    {
        $sql = "INSERT INTO {$this->table} 
                (user_id, field_id, date, start_time, duration_minutes, price_total, status) 
                VALUES (:user_id, :field_id, :date, :start_time, :duration_minutes, :price_total, :status)";
        $this->execute($sql, [
            'user_id' => $data['user_id'],
            'field_id' => $data['field_id'],
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'duration_minutes' => $data['duration_minutes'],
            'price_total' => $data['price_total'],
            'status' => $data['status'] ?? 'pending'
        ]);
        return $this->lastInsertId();
    }

    /**
     * Verifica si existe una reserva en conflicto
     *
     * @param int $fieldId
     * @param string $date
     * @param string $startTime
     * @param int $durationMinutes
     * @param int|null $excludeBookingId
     * @return array|false
     */
    public function findConflict(int $fieldId, string $date, string $startTime, int $durationMinutes, ?int $excludeBookingId = null)
    {
        // Calcular hora de fin
        $start = strtotime("{$date} {$startTime}");
        $end = $start + ($durationMinutes * 60);
        $endTime = date('H:i:s', $end);

        $sql = "SELECT * FROM {$this->table} 
                WHERE field_id = :field_id 
                AND date = :date 
                AND status IN ('pending', 'confirmed')
                AND (
                    (start_time >= :start_time AND start_time < :end_time) OR
                    (ADDTIME(start_time, SEC_TO_TIME(duration_minutes * 60)) > :start_time 
                     AND ADDTIME(start_time, SEC_TO_TIME(duration_minutes * 60)) <= :end_time) OR
                    (start_time <= :start_time AND ADDTIME(start_time, SEC_TO_TIME(duration_minutes * 60)) >= :end_time)
                )";
        
        $params = [
            'field_id' => $fieldId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime
        ];

        if ($excludeBookingId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeBookingId;
        }

        return $this->queryOne($sql, $params);
    }

    /**
     * Obtiene todas las reservas con filtros
     *
     * @param array $filters
     * @return array
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT b.*, u.nombre as user_nombre, u.email as user_email, f.nombre as field_nombre 
                FROM {$this->table} b
                INNER JOIN users u ON b.user_id = u.id
                INNER JOIN fields f ON b.field_id = f.id
                WHERE 1=1";
        $params = [];

        if (isset($filters['user_id'])) {
            $sql .= " AND b.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (isset($filters['field_id'])) {
            $sql .= " AND b.field_id = :field_id";
            $params['field_id'] = $filters['field_id'];
        }

        if (isset($filters['status'])) {
            $sql .= " AND b.status = :status";
            $params['status'] = $filters['status'];
        }

        if (isset($filters['date_from'])) {
            $sql .= " AND b.date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (isset($filters['date_to'])) {
            $sql .= " AND b.date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY b.date DESC, b.start_time DESC";

        return $this->query($sql, $params);
    }

    /**
     * Obtiene una reserva por ID
     *
     * @param int $id
     * @return array|false
     */
    public function findById(int $id)
    {
        return $this->queryOne(
            "SELECT b.*, u.nombre as user_nombre, u.email as user_email, f.nombre as field_nombre, f.ubicacion as field_ubicacion
             FROM {$this->table} b
             INNER JOIN users u ON b.user_id = u.id
             INNER JOIN fields f ON b.field_id = f.id
             WHERE b.id = :id",
            ['id' => $id]
        );
    }

    /**
     * Actualiza el estado de una reserva
     *
     * @param int $id
     * @param string $status
     * @param string|null $cancelReason
     * @return bool
     */
    public function updateStatus(int $id, string $status, ?string $cancelReason = null): bool
    {
        $sql = "UPDATE {$this->table} SET status = :status, cancel_reason = :cancel_reason WHERE id = :id";
        return $this->execute($sql, [
            'status' => $status,
            'cancel_reason' => $cancelReason,
            'id' => $id
        ]);
    }

    /**
     * Obtiene las reservas de una cancha en una fecha específica
     *
     * @param int $fieldId
     * @param string $date
     * @return array
     */
    public function getByFieldAndDate(int $fieldId, string $date): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE field_id = :field_id 
             AND date = :date 
             AND status IN ('pending', 'confirmed')
             ORDER BY start_time",
            [
                'field_id' => $fieldId,
                'date' => $date
            ]
        );
    }
}

