<?php

namespace App\Helpers;

/**
 * Helper para sanitización de datos
 */
class SanitizeHelper
{
    /**
     * Sanitiza un string
     *
     * @param mixed $value
     * @return string
     */
    public static function string($value): string
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }
        return trim(htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Sanitiza un email
     *
     * @param mixed $value
     * @return string
     */
    public static function email($value): string
    {
        return filter_var(trim($value), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitiza un número entero
     *
     * @param mixed $value
     * @return int
     */
    public static function int($value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitiza un número decimal
     *
     * @param mixed $value
     * @return float
     */
    public static function float($value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitiza un array de datos
     *
     * @param array $data
     * @return array
     */
    public static function array(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $key = self::string($key);
            if (is_array($value)) {
                $sanitized[$key] = self::array($value);
            } else {
                $sanitized[$key] = self::string($value);
            }
        }
        return $sanitized;
    }
}

