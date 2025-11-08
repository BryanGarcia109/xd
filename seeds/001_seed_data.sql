-- =====================================================
-- Seed Data - Datos de Prueba
-- =====================================================
-- Ejecutar: mysql -u root -p canchas_db < seeds/001_seed_data.sql
-- =====================================================

-- Insertar Usuario Admin
-- Password: admin123 (hash generado con password_hash)
INSERT INTO `users` (`nombre`, `email`, `telefono`, `password_hash`, `role`) VALUES
('Administrador', 'admin@canchas.com', '999888777', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insertar Usuarios Clientes
-- Password: cliente123
INSERT INTO `users` (`nombre`, `email`, `telefono`, `password_hash`, `role`) VALUES
('Juan Pérez', 'juan@example.com', '987654321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client'),
('María González', 'maria@example.com', '987654322', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client');

-- Insertar Canchas
INSERT INTO `fields` (`nombre`, `descripcion`, `ubicacion`, `tipo`, `dimensiones`, `price_per_hour`, `photo_url`, `status`) VALUES
('Cancha 1 - Fútbol 7', 'Cancha sintética de fútbol 7 con iluminación LED', 'Av. Principal 123, Lima', 'sintética', '70m x 50m', 80.00, 'https://example.com/cancha1.jpg', 'active'),
('Cancha 2 - Fútbol 5', 'Cancha sintética de fútbol 5, ideal para grupos pequeños', 'Jr. Los Olivos 456, Lima', 'sintética', '40m x 20m', 60.00, 'https://example.com/cancha2.jpg', 'active'),
('Cancha 3 - Fútbol 11', 'Cancha sintética profesional de fútbol 11', 'Av. Universitaria 789, Lima', 'sintética', '105m x 68m', 120.00, 'https://example.com/cancha3.jpg', 'active');

-- Insertar Horarios para Cancha 1
-- Lunes a Viernes: 08:00 - 22:00
INSERT INTO `field_schedules` (`field_id`, `day_of_week`, `start_time`, `end_time`, `duration_minutes`, `active`) VALUES
(1, 1, '08:00:00', '22:00:00', 60, TRUE),  -- Lunes
(1, 2, '08:00:00', '22:00:00', 60, TRUE),  -- Martes
(1, 3, '08:00:00', '22:00:00', 60, TRUE),  -- Miércoles
(1, 4, '08:00:00', '22:00:00', 60, TRUE),  -- Jueves
(1, 5, '08:00:00', '22:00:00', 60, TRUE);  -- Viernes

-- Sábado y Domingo: 09:00 - 20:00
INSERT INTO `field_schedules` (`field_id`, `day_of_week`, `start_time`, `end_time`, `duration_minutes`, `active`) VALUES
(1, 6, '09:00:00', '20:00:00', 60, TRUE),  -- Sábado
(1, 0, '09:00:00', '20:00:00', 60, TRUE);  -- Domingo

-- Insertar Horarios para Cancha 2
INSERT INTO `field_schedules` (`field_id`, `day_of_week`, `start_time`, `end_time`, `duration_minutes`, `active`) VALUES
(2, 1, '08:00:00', '22:00:00', 60, TRUE),
(2, 2, '08:00:00', '22:00:00', 60, TRUE),
(2, 3, '08:00:00', '22:00:00', 60, TRUE),
(2, 4, '08:00:00', '22:00:00', 60, TRUE),
(2, 5, '08:00:00', '22:00:00', 60, TRUE),
(2, 6, '09:00:00', '20:00:00', 60, TRUE),
(2, 0, '09:00:00', '20:00:00', 60, TRUE);

-- Insertar Horarios para Cancha 3
INSERT INTO `field_schedules` (`field_id`, `day_of_week`, `start_time`, `end_time`, `duration_minutes`, `active`) VALUES
(3, 1, '08:00:00', '22:00:00', 60, TRUE),
(3, 2, '08:00:00', '22:00:00', 60, TRUE),
(3, 3, '08:00:00', '22:00:00', 60, TRUE),
(3, 4, '08:00:00', '22:00:00', 60, TRUE),
(3, 5, '08:00:00', '22:00:00', 60, TRUE),
(3, 6, '09:00:00', '20:00:00', 60, TRUE),
(3, 0, '09:00:00', '20:00:00', 60, TRUE);

-- Nota: Las contraseñas de ejemplo son 'admin123' y 'cliente123'
-- En producción, generar hashes únicos con password_hash()

