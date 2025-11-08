<?php

namespace App\Models;

/**
 * Modelo de Pago
 */
class Payment extends BaseModel
{
    protected string $table = 'payments';

    /**
     * Crea un nuevo pago
     *
     * @param array $data
     * @return string ID del pago creado
     */
    public function create(array $data): string
    {
        $sql = "INSERT INTO {$this->table} 
                (booking_id, method, amount, status, payment_external_id) 
                VALUES (:booking_id, :method, :amount, :status, :payment_external_id)";
        $this->execute($sql, [
            'booking_id' => $data['booking_id'],
            'method' => $data['method'],
            'amount' => $data['amount'],
            'status' => $data['status'] ?? 'pending',
            'payment_external_id' => $data['payment_external_id'] ?? null
        ]);
        return $this->lastInsertId();
    }

    /**
     * Obtiene un pago por ID
     *
     * @param int $id
     * @return array|false
     */
    public function findById(int $id)
    {
        return $this->queryOne(
            "SELECT p.*, b.user_id, b.field_id, b.date, b.start_time 
             FROM {$this->table} p
             INNER JOIN bookings b ON p.booking_id = b.id
             WHERE p.id = :id",
            ['id' => $id]
        );
    }

    /**
     * Obtiene pagos por reserva
     *
     * @param int $bookingId
     * @return array
     */
    public function getByBookingId(int $bookingId): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE booking_id = :booking_id ORDER BY created_at DESC",
            ['booking_id' => $bookingId]
        );
    }

    /**
     * Actualiza el estado de un pago
     *
     * @param int $id
     * @param string $status
     * @param string|null $externalId
     * @return bool
     */
    public function updateStatus(int $id, string $status, ?string $externalId = null): bool
    {
        $sql = "UPDATE {$this->table} SET status = :status";
        $params = ['status' => $status, 'id' => $id];

        if ($externalId !== null) {
            $sql .= ", payment_external_id = :external_id";
            $params['external_id'] = $externalId;
        }

        $sql .= " WHERE id = :id";
        return $this->execute($sql, $params);
    }

    /**
     * Obtiene pagos con información de reservas y canchas
     *
     * @param array $filters
     * @return array
     */
    public function getWithDetails(array $filters = []): array
    {
        $sql = "SELECT p.*, b.field_id, b.date, b.start_time, b.user_id, f.nombre as field_nombre
                FROM {$this->table} p
                INNER JOIN bookings b ON p.booking_id = b.id
                INNER JOIN fields f ON b.field_id = f.id
                WHERE 1=1";
        $params = [];

        if (isset($filters['status'])) {
            $sql .= " AND p.status = :status";
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

        $sql .= " ORDER BY b.date DESC";

        return $this->query($sql, $params);
    }
}

