<?php

/**
 * Script de Seed
 * Pobla la base de datos con datos de ejemplo
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

echo "Ejecutando seeds...\n";

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Leer y ejecutar seeds
    $seedFile = __DIR__ . '/../seeds/001_seed_data.sql';
    if (file_exists($seedFile)) {
        $sql = file_get_contents($seedFile);
        $pdo->exec($sql);
        echo "✓ Seed 001_seed_data.sql ejecutado correctamente\n";
    }

    echo "Seeds completados.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

