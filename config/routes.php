<?php

/**
 * Definición de Rutas
 */

return [
    // Autenticación
    'POST /api/auth/register' => ['App\Controllers\AuthController', 'register'],
    'POST /api/auth/login' => ['App\Controllers\AuthController', 'login'],
    'POST /api/auth/forgot-password' => ['App\Controllers\AuthController', 'forgotPassword'],
    'POST /api/auth/reset-password' => ['App\Controllers\AuthController', 'resetPassword'],
    'GET /api/auth/profile' => ['App\Controllers\AuthController', 'profile'],
    'PUT /api/auth/profile' => ['App\Controllers\AuthController', 'updateProfile'],

    // Canchas
    'GET /api/fields' => ['App\Controllers\FieldController', 'index'],
    'GET /api/fields/{id}' => ['App\Controllers\FieldController', 'show'],
    'POST /api/fields' => ['App\Controllers\FieldController', 'create'],
    'PUT /api/fields/{id}' => ['App\Controllers\FieldController', 'update'],
    'DELETE /api/fields/{id}' => ['App\Controllers\FieldController', 'delete'],
    'GET /api/fields/{id}/availability' => ['App\Controllers\FieldController', 'availability'],

    // Reservas
    'GET /api/bookings' => ['App\Controllers\BookingController', 'index'],
    'GET /api/bookings/{id}' => ['App\Controllers\BookingController', 'show'],
    'POST /api/bookings' => ['App\Controllers\BookingController', 'create'],
    'PUT /api/bookings/{id}/cancel' => ['App\Controllers\BookingController', 'cancel'],

    // Pagos
    'POST /api/payments' => ['App\Controllers\PaymentController', 'process'],
    'GET /api/payments/{id}' => ['App\Controllers\PaymentController', 'show'],
    'GET /api/payments/booking/{id}' => ['App\Controllers\PaymentController', 'getByBooking'],

    // Administración
    'GET /api/admin/reports/bookings' => ['App\Controllers\AdminController', 'reportBookings'],
    'GET /api/admin/reports/revenue' => ['App\Controllers\AdminController', 'reportRevenue'],

    // Documentación Swagger
    'GET /api/docs' => ['App\Controllers\SwaggerController', 'index'],
    'GET /api/docs/openapi.json' => ['App\Controllers\SwaggerController', 'openapi'],
];

