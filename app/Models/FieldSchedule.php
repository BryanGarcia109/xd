<?php

namespace App\Models;

/**
 * Modelo de Horario de Cancha
 */
class FieldSchedule extends BaseModel
{
    protected string $table = 'field_schedules';

    /**
     * Obtiene los horarios activos de una cancha
     *
     * @param int $fieldId
     * @return array
     */
    public function getByFieldId(int $fieldId): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE field_id = :field_id AND active = TRUE ORDER BY day_of_week, start_time",
            ['field_id' => $fieldId]
        );
    }

    /**
     * Obtiene el horario de una cancha para un día específico
     *
     * @param int $fieldId
     * @param int|null $dayOfWeek
     * @param string|null $fechaEspecifica
     * @return array
     */
    public function getScheduleForDay(int $fieldId, ?int $dayOfWeek = null, ?string $fechaEspecifica = null): array
    {
        if ($fechaEspecifica) {
            return $this->query(
                "SELECT * FROM {$this->table} 
                 WHERE field_id = :field_id 
                 AND (fecha_especifica = :fecha OR (day_of_week = :day AND fecha_especifica IS NULL))
                 AND active = TRUE
                 ORDER BY start_time",
                [
                    'field_id' => $fieldId,
                    'fecha' => $fechaEspecifica,
                    'day' => $dayOfWeek
                ]
            );
        }

        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE field_id = :field_id 
             AND day_of_week = :day 
             AND fecha_especifica IS NULL
             AND active = TRUE
             ORDER BY start_time",
            [
                'field_id' => $fieldId,
                'day' => $dayOfWeek
            ]
        );
    }

    /**
     * Crea un nuevo horario
     *
     * @param array $data
     * @return string ID del horario creado
     */
    public function create(array $data): string
    {
        $sql = "INSERT INTO {$this->table} 
                (field_id, day_of_week, fecha_especifica, start_time, end_time, duration_minutes, active) 
                VALUES (:field_id, :day_of_week, :fecha_especifica, :start_time, :end_time, :duration_minutes, :active)";
        $this->execute($sql, [
            'field_id' => $data['field_id'],
            'day_of_week' => $data['day_of_week'] ?? null,
            'fecha_especifica' => $data['fecha_especifica'] ?? null,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'active' => $data['active'] ?? true
        ]);
        return $this->lastInsertId();
    }

    /**
     * Actualiza un horario
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        $allowedFields = ['day_of_week', 'fecha_especifica', 'start_time', 'end_time', 'duration_minutes', 'active'];
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
}

