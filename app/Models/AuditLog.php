<?php

namespace App\Models;

/**
 * Modelo de Log de Auditoría
 */
class AuditLog extends BaseModel
{
    protected string $table = 'audit_logs';

    /**
     * Registra una acción en el log
     *
     * @param array $data
     * @return string ID del log creado
     */
    public function create(array $data): string
    {
        $sql = "INSERT INTO {$this->table} (user_id, action, data, ip) 
                VALUES (:user_id, :action, :data, :ip)";
        $this->execute($sql, [
            'user_id' => $data['user_id'] ?? null,
            'action' => $data['action'],
            'data' => json_encode($data['data'] ?? []),
            'ip' => $data['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? null
        ]);
        return $this->lastInsertId();
    }

    /**
     * Obtiene logs con filtros
     *
     * @param array $filters
     * @return array
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT al.*, u.nombre as user_nombre, u.email as user_email 
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE 1=1";
        $params = [];

        if (isset($filters['user_id'])) {
            $sql .= " AND al.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (isset($filters['action'])) {
            $sql .= " AND al.action = :action";
            $params['action'] = $filters['action'];
        }

        if (isset($filters['date_from'])) {
            $sql .= " AND al.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (isset($filters['date_to'])) {
            $sql .= " AND al.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT 1000";

        return $this->query($sql, $params);
    }
}

