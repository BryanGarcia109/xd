<?php

namespace App\Models;

/**
 * Modelo de Usuario
 */
class User extends BaseModel
{
    protected string $table = 'users';

    /**
     * Busca un usuario por email
     *
     * @param string $email
     * @return array|false
     */
    public function findByEmail(string $email)
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE email = :email",
            ['email' => $email]
        );
    }

    /**
     * Busca un usuario por ID
     *
     * @param int $id
     * @return array|false
     */
    public function findById(int $id)
    {
        return $this->queryOne(
            "SELECT id, nombre, email, telefono, role, created_at, updated_at FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Crea un nuevo usuario
     *
     * @param array $data
     * @return string ID del usuario creado
     */
    public function create(array $data): string
    {
        $sql = "INSERT INTO {$this->table} (nombre, email, telefono, password_hash, role) 
                VALUES (:nombre, :email, :telefono, :password_hash, :role)";
        $this->execute($sql, [
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'telefono' => $data['telefono'],
            'password_hash' => $data['password_hash'],
            'role' => $data['role'] ?? 'client'
        ]);
        return $this->lastInsertId();
    }

    /**
     * Actualiza el perfil del usuario
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProfile(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['nombre'])) {
            $fields[] = 'nombre = :nombre';
            $params['nombre'] = $data['nombre'];
        }
        if (isset($data['telefono'])) {
            $fields[] = 'telefono = :telefono';
            $params['telefono'] = $data['telefono'];
        }
        if (isset($data['password_hash'])) {
            $fields[] = 'password_hash = :password_hash';
            $params['password_hash'] = $data['password_hash'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        return $this->execute($sql, $params);
    }

    /**
     * Guarda el token de recuperación de contraseña
     *
     * @param string $email
     * @param string $token
     * @param string $expires
     * @return bool
     */
    public function saveResetToken(string $email, string $token, string $expires): bool
    {
        $sql = "UPDATE {$this->table} SET reset_token = :token, reset_expires = :expires WHERE email = :email";
        return $this->execute($sql, [
            'token' => $token,
            'expires' => $expires,
            'email' => $email
        ]);
    }

    /**
     * Busca usuario por token de recuperación
     *
     * @param string $token
     * @return array|false
     */
    public function findByResetToken(string $token)
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE reset_token = :token AND reset_expires > NOW()",
            ['token' => $token]
        );
    }

    /**
     * Limpia el token de recuperación
     *
     * @param string $email
     * @return bool
     */
    public function clearResetToken(string $email): bool
    {
        $sql = "UPDATE {$this->table} SET reset_token = NULL, reset_expires = NULL WHERE email = :email";
        return $this->execute($sql, ['email' => $email]);
    }
}

