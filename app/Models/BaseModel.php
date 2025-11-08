<?php

namespace App\Models;

use PDO;
use PDOException;

/**
 * Modelo Base
 * Proporciona acceso a la base de datos mediante PDO
 */
class BaseModel
{
    protected PDO $db;
    protected string $table;

    /**
     * Constructor
     * Establece la conexión a la base de datos
     */
    public function __construct()
    {
        $this->db = $this->getConnection();
    }

    /**
     * Obtiene la conexión PDO a la base de datos
     *
     * @return PDO
     * @throws PDOException
     */
    protected function getConnection(): PDO
    {
        $config = require __DIR__ . '/../../config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['dbname'],
            $config['charset']
        );

        try {
            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
            return $pdo;
        } catch (PDOException $e) {
            throw new PDOException('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    /**
     * Ejecuta una consulta SELECT
     *
     * @param string $sql Consulta SQL
     * @param array $params Parámetros para la consulta
     * @return array
     */
    protected function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new PDOException('Error en la consulta: ' . $e->getMessage());
        }
    }

    /**
     * Ejecuta una consulta SELECT y retorna un solo registro
     *
     * @param string $sql Consulta SQL
     * @param array $params Parámetros para la consulta
     * @return array|false
     */
    protected function queryOne(string $sql, array $params = [])
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new PDOException('Error en la consulta: ' . $e->getMessage());
        }
    }

    /**
     * Ejecuta una consulta INSERT, UPDATE o DELETE
     *
     * @param string $sql Consulta SQL
     * @param array $params Parámetros para la consulta
     * @return bool
     */
    protected function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new PDOException('Error en la ejecución: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene el último ID insertado
     *
     * @return string
     */
    protected function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }
}

