<?php

/**
 * Configuración General de la Aplicación
 */

return [
    'env' => getenv('APP_ENV') ?: 'local',
    'base_url' => getenv('BASE_URL') ?: 'http://localhost:8000',
    'timezone' => 'America/Lima',
    'debug' => (getenv('APP_ENV') ?: 'local') === 'local',
];

