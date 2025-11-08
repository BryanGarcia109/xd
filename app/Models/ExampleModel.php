<?php

namespace App\Models;

/**
 * Modelo de Ejemplo
 * Muestra cómo extender BaseModel
 */
class ExampleModel extends BaseModel
{
    protected string $table = 'example';

    /**
     * Obtiene todos los registros
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->query("SELECT * FROM {$this->table} ORDER BY id DESC");
    }

    /**
     * Obtiene un registro por ID
     *
     * @param int $id ID del registro
     * @return array|false
     */
    public function getById(int $id)
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Crea un nuevo registro
     *
     * @param array $data Datos del registro
     * @return string ID del registro insertado
     */
    public function create(array $data): string
    {
        $sql = "INSERT INTO {$this->table} (name) VALUES (:name)";
        $this->execute($sql, ['name' => $data['name']]);
        return $this->lastInsertId();
    }
}

