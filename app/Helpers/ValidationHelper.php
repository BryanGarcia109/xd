<?php

namespace App\Helpers;

/**
 * Helper para validaciones
 */
class ValidationHelper
{
    /**
     * Valida si un campo está presente en los datos
     *
     * @param array $data Datos a validar
     * @param string $field Campo a validar
     * @return bool
     */
    public static function required(array $data, string $field): bool
    {
        return isset($data[$field]) && !empty(trim($data[$field]));
    }

    /**
     * Valida si un campo es un email válido
     *
     * @param string $email Email a validar
     * @return bool
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida si un campo tiene una longitud mínima
     *
     * @param string $value Valor a validar
     * @param int $minLength Longitud mínima
     * @return bool
     */
    public static function minLength(string $value, int $minLength): bool
    {
        return strlen($value) >= $minLength;
    }

    /**
     * Valida si un campo tiene una longitud máxima
     *
     * @param string $value Valor a validar
     * @param int $maxLength Longitud máxima
     * @return bool
     */
    public static function maxLength(string $value, int $maxLength): bool
    {
        return strlen($value) <= $maxLength;
    }
}

