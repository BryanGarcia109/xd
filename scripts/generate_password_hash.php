<?php

/**
 * Script para generar hash de contraseñas
 * Útil para actualizar los seeds con contraseñas reales
 */

if ($argc < 2) {
    echo "Uso: php scripts/generate_password_hash.php <password>\n";
    exit(1);
}

$password = $argv[1];
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: {$password}\n";
echo "Hash: {$hash}\n";

