<?php

/**
 * Script de Migración
 * Ejecuta todas las migraciones en orden
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'canchas_db';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

echo "Ejecutando migraciones...\n";

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE {$dbName}");

    // Leer y ejecutar migraciones
    $migrationFile = __DIR__ . '/001_init.sql';
    if (file_exists($migrationFile)) {
        $sql = file_get_contents($migrationFile);
        $pdo->exec($sql);
        echo "✓ Migración 001_init.sql ejecutada correctamente\n";
    }

    echo "Migraciones completadas.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

