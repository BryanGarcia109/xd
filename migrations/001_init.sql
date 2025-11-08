-- =====================================================
-- Migración Inicial - Sistema de Gestión de Canchas
-- =====================================================
-- Ejecutar: mysql -u root -p canchas_db < migrations/001_init.sql
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `field_schedules`;
DROP TABLE IF EXISTS `fields`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- Tabla de Usuarios
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `telefono` VARCHAR(20) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('client', 'admin') DEFAULT 'client',
    `reset_token` VARCHAR(255) NULL,
    `reset_expires` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Canchas
CREATE TABLE `fields` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT NULL,
    `ubicacion` VARCHAR(255) NOT NULL,
    `tipo` VARCHAR(50) DEFAULT 'sintética',
    `dimensiones` VARCHAR(100) NULL,
    `price_per_hour` DECIMAL(10, 2) NOT NULL,
    `photo_url` VARCHAR(500) NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_ubicacion` (`ubicacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Horarios de Canchas
CREATE TABLE `field_schedules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `field_id` INT NOT NULL,
    `day_of_week` INT NULL COMMENT '0=Domingo, 1=Lunes, ..., 6=Sábado. NULL si es fecha específica',
    `fecha_especifica` DATE NULL COMMENT 'Fecha específica (si day_of_week es NULL)',
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `duration_minutes` INT DEFAULT 60 COMMENT 'Duración de cada franja en minutos',
    `active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`field_id`) REFERENCES `fields`(`id`) ON DELETE CASCADE,
    INDEX `idx_field_id` (`field_id`),
    INDEX `idx_day_of_week` (`day_of_week`),
    INDEX `idx_fecha_especifica` (`fecha_especifica`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Reservas
CREATE TABLE `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `field_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `duration_minutes` INT NOT NULL,
    `price_total` DECIMAL(10, 2) NOT NULL,
    `status` ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    `cancel_reason` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`field_id`) REFERENCES `fields`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_field_id` (`field_id`),
    INDEX `idx_date` (`date`),
    INDEX `idx_status` (`status`),
    UNIQUE KEY `unique_booking` (`field_id`, `date`, `start_time`, `status`) COMMENT 'Evita dobles reservas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Pagos
CREATE TABLE `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL,
    `method` VARCHAR(50) NOT NULL COMMENT 'cash, card, transfer, etc.',
    `amount` DECIMAL(10, 2) NOT NULL,
    `status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    `payment_external_id` VARCHAR(255) NULL COMMENT 'ID del pago en gateway externo',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
    INDEX `idx_booking_id` (`booking_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Logs de Auditoría
CREATE TABLE `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `data` JSON NULL,
    `ip` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

