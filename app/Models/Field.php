<?php

namespace App\Models;

/**
 * Modelo de Cancha
 */
class Field extends BaseModel
{
    protected string $table = 'fields';

    /**
     * Obtiene todas las canchas activas
     *
     * @param array $filters
     * @return array
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (isset($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        if (isset($filters['ubicacion'])) {
            $sql .= " AND ubicacion LIKE :ubicacion";
            $params['ubicacion'] = '%' . $filters['ubicacion'] . '%';
        }

        if (isset($filters['tipo'])) {
            $sql .= " AND tipo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }

        if (isset($filters['min_price'])) {
            $sql .= " AND price_per_hour >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }

        if (isset($filters['max_price'])) {
            $sql .= " AND price_per_hour <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        $sql .= " ORDER BY nombre ASC";

        return $this->query($sql, $params);
    }

    /**
     * Obtiene una cancha por ID
     *
     * @param int $id
     * @return array|false
     */
    public function findById(int $id)
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Crea una nueva cancha
     *
     * @param array $data
     * @return string ID de la cancha creada
     */
    public function create(array $data): string
    {
        $sql = "INSERT INTO {$this->table} 
                (nombre, descripcion, ubicacion, tipo, dimensiones, price_per_hour, photo_url, status) 
                VALUES (:nombre, :descripcion, :ubicacion, :tipo, :dimensiones, :price_per_hour, :photo_url, :status)";
        $this->execute($sql, [
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'ubicacion' => $data['ubicacion'],
            'tipo' => $data['tipo'] ?? 'sintética',
            'dimensiones' => $data['dimensiones'] ?? null,
            'price_per_hour' => $data['price_per_hour'],
            'photo_url' => $data['photo_url'] ?? null,
            'status' => $data['status'] ?? 'active'
        ]);
        return $this->lastInsertId();
    }

    /**
     * Actualiza una cancha
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        $allowedFields = ['nombre', 'descripcion', 'ubicacion', 'tipo', 'dimensiones', 'price_per_hour', 'photo_url', 'status'];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        return $this->execute($sql, $params);
    }

    /**
     * Elimina una cancha (soft delete cambiando status)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET status = 'inactive' WHERE id = :id";
        return $this->execute($sql, ['id' => $id]);
    }
}

