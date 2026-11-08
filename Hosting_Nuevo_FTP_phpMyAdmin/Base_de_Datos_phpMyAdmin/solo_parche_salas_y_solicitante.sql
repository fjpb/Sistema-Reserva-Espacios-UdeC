-- ============================================================================
-- MIGRACIÓN Y ACTUALIZACIÓN DE SALAS Y ESPACIOS ACADÉMICOS
-- Universidad de Concepción - Campus Los Ángeles
-- Fuente oficial: Documentos/Salas y edificios, cantidad y descripción.xlsx
-- ============================================================================
-- Características de seguridad (@database / @legacy):
-- 1. Preserva el 100% de los IDs existentes en `mrbs_area` y `mrbs_room`,
--    manteniendo intactas las +28.500 reservas históricas (`mrbs_entry` y `mrbs_repeat`).
-- 2. Amplía `mrbs_room.description` a VARCHAR(150) para soportar descripciones
--    detalladas de equipamiento sin truncamiento.
-- 3. Operación 100% idempotente: puede ejecutarse múltiples veces sin duplicar
--    áreas ni salas nuevas.
-- ============================================================================

SET NAMES utf8mb4;
START TRANSACTION;

-- 0. Ampliar columna de descripción y agregar columna personalizada 'solicitante'
ALTER TABLE `mrbs_room`
  MODIFY COLUMN `description` VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL;

ALTER TABLE `mrbs_entry`
  ADD COLUMN IF NOT EXISTS `solicitante` VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '';

ALTER TABLE `mrbs_repeat`
  ADD COLUMN IF NOT EXISTS `solicitante` VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '';

-- ============================================================================
-- 1. ACTUALIZACIÓN DE CAPACIDADES Y EQUIPAMIENTO EN SALAS EXISTENTES
-- ============================================================================

-- --- Edificio de Aulas (area_id = 5) ---
UPDATE `mrbs_room` SET `capacity` = 70, `description` = 'Tipo: Híbrida | Pantalla Táctil, Audio AUX' WHERE `id` = 15; -- Sala 11
UPDATE `mrbs_room` SET `capacity` = 70, `description` = 'Tipo: Híbrida | Pantalla Táctil' WHERE `id` = 16;            -- Sala 12
UPDATE `mrbs_room` SET `capacity` = 40, `description` = 'Tipo: AIO | Pantalla Táctil, PC, TV' WHERE `id` = 17;        -- Sala 13
UPDATE `mrbs_room` SET `capacity` = 40, `description` = 'Tipo: Normal | Proyector' WHERE `id` = 18;                   -- Sala 14
UPDATE `mrbs_room` SET `capacity` = 69, `description` = 'Tipo: AIO | PC, Audio BT, TV' WHERE `id` = 19;               -- Sala 21
UPDATE `mrbs_room` SET `capacity` = 69, `description` = 'Tipo: Normal | Proyector' WHERE `id` = 20;                   -- Sala 22
UPDATE `mrbs_room` SET `capacity` = 40, `description` = 'Tipo: Normal | Proyector' WHERE `id` = 21;                   -- Sala 23
UPDATE `mrbs_room` SET `capacity` = 40, `description` = 'Tipo: Normal | Proyector' WHERE `id` = 22;                   -- Sala 24
UPDATE `mrbs_room` SET `capacity` = 69, `description` = 'Tipo: Normal | Proyector' WHERE `id` = 23;                   -- Sala 31
UPDATE `mrbs_room` SET `capacity` = 30, `description` = 'Tipo: Normal | Proyector' WHERE `id` = 24;                   -- Sala 32
UPDATE `mrbs_room` SET `capacity` = 40, `description` = 'Tipo: Normal | Proyector' WHERE `id` = 25;                   -- Sala 33
UPDATE `mrbs_room` SET `capacity` = 40, `description` = 'Tipo: Normal | Proyector' WHERE `id` = 26;                   -- Sala 34

-- --- Edificio de Artes (area_id = 4) ---
UPDATE `mrbs_room` SET `capacity` = 69, `description` = 'Tipo: AIO | Proyector, PC, Audio BT, A/C' WHERE `id` = 10;   -- Sala 1
UPDATE `mrbs_room` SET `capacity` = 40, `description` = 'Tipo: AIO | Proyector, PC, A/C' WHERE `id` = 11;             -- Sala 2
UPDATE `mrbs_room` SET `capacity` = 40, `description` = 'Tipo: AIO | Proyector, PC, A/C' WHERE `id` = 12;             -- Sala 3
UPDATE `mrbs_room` SET `capacity` = 60, `description` = 'Tipo: AIO | Proyector, PC, Audio BT, A/C' WHERE `id` = 13;   -- Sala 4
UPDATE `mrbs_room` SET `capacity` = 30, `description` = 'Tipo: AIO | Proyector, PC, A/C' WHERE `id` = 14;             -- Sala Experiencia Ped.

-- --- Ed. Enrique Molina Garmendia (area_id = 14) ---
UPDATE `mrbs_room` SET `room_name` = 'Sala 411', `sort_key` = 'Sala 411', `capacity` = 90, `description` = 'Tipo: Híbrida | Pantalla Táctil, A/C' WHERE `id` = 45;
UPDATE `mrbs_room` SET `room_name` = 'Sala 412', `sort_key` = 'Sala 412', `capacity` = 80, `description` = 'Tipo: Híbrida | Pantalla Táctil, A/C' WHERE `id` = 46;
UPDATE `mrbs_room` SET `room_name` = 'Sala 421', `sort_key` = 'Sala 421', `capacity` = 70, `description` = 'Tipo: AIO | Proyector, Pantalla Táctil, PC, Audio BT, A/C' WHERE `id` = 47;
UPDATE `mrbs_room` SET `room_name` = 'Sala 422', `sort_key` = 'Sala 422', `capacity` = 70, `description` = 'Tipo: AIO | PC, A/C, TV' WHERE `id` = 48;

-- --- Pasillo Central (area_id = 3) ---
UPDATE `mrbs_room` SET `capacity` = 40,  `description` = 'Tipo: AIO | Proyector, PC, Audio AUX, A/C' WHERE `id` = 5;   -- Sala 91
UPDATE `mrbs_room` SET `capacity` = 100, `description` = 'Tipo: Híbrida | Pantalla Táctil, A/C' WHERE `id` = 6;        -- Sala 93A
UPDATE `mrbs_room` SET `capacity` = 65,  `description` = 'Tipo: AIO | Proyector, PC, A/C' WHERE `id` = 7;              -- Sala 93B
UPDATE `mrbs_room` SET `capacity` = 100, `description` = 'Tipo: Híbrida | Pantalla Táctil, Audio BT, A/C' WHERE `id` = 8; -- Sala 94
UPDATE `mrbs_room` SET `capacity` = 45,  `description` = 'Tipo: AIO | Proyector, PC, A/C' WHERE `id` = 9;              -- Sala 106B
UPDATE `mrbs_room` SET `capacity` = 20,  `description` = 'Tipo: Híbrida | Pantalla Táctil, PC, A/C' WHERE `id` = 60;   -- Sala de Postgrado

-- --- Salas Estacionamiento Norte (area_id = 8) ---
UPDATE `mrbs_room` SET `capacity` = 35, `description` = 'Tipo: AIO | Proyector, PC' WHERE `id` = 32;                   -- Sala 103
UPDATE `mrbs_room` SET `capacity` = 45, `description` = 'Tipo: Híbrida | Pantalla Táctil' WHERE `id` = 33;             -- Sala 105
UPDATE `mrbs_room` SET `capacity` = 30, `description` = 'Tipo: Híbrida | Pantalla Táctil' WHERE `id` = 34;             -- Sala Recursos Didacticos (SRD)

-- --- Laboratorios de Computación (area_id = 10) ---
UPDATE `mrbs_room` SET `capacity` = 36, `description` = 'PC, Audio BT, A/C, TV' WHERE `id` = 36;                       -- Lab. Ciencias y Tec.
UPDATE `mrbs_room` SET `capacity` = 32, `description` = 'Tipo: Híbrido | Pantalla Táctil, PC, A/C' WHERE `id` = 37;    -- Lab. de Educación
UPDATE `mrbs_room` SET `capacity` = 23, `description` = 'Tipo: Normal | Proyector, A/C' WHERE `id` = 38;               -- Lab. de Inglés (Idiomas)

-- --- Auditorios (area_id = 11) ---
UPDATE `mrbs_room` SET `capacity` = 120, `description` = 'Tipo: Normal | Proyector, Audio AUX, A/C' WHERE `id` = 39;   -- Auditorio Ronald Ramm
UPDATE `mrbs_room` SET `capacity` = 130, `description` = 'Tipo: Normal | PC, Audio AUX, A/C, TV (3x3)' WHERE `id` = 40; -- Auditorio Manuel Rioseco

-- --- Sala APTC (area_id = 12) ---
UPDATE `mrbs_room` SET `capacity` = 60, `description` = 'Tipo: AIO | PC, A/C, TV (4)' WHERE `id` = 41;                 -- Sala APTC

-- --- Salas de Reuniones (area_id = 7) ---
UPDATE `mrbs_room` SET `capacity` = 15, `description` = 'Tipo: Normal | A/C, TV' WHERE `id` = 29;                      -- Sala Pedro Rios
UPDATE `mrbs_room` SET `capacity` = 15, `description` = 'Tipo: Híbrida | Pantalla Táctil, PC, Audio AUX/BT, A/C, TV' WHERE `id` = 30; -- Sala Reunión Dirección
UPDATE `mrbs_room` SET `capacity` = 15, `description` = 'Tipo: Normal | A/C, TV' WHERE `id` = 31;                      -- Sala Juan Contreras

-- --- Cowork (area_id = 16) ---
UPDATE `mrbs_room` SET `capacity` = 6,  `description` = 'Tipo: Normal | A/C, TV' WHERE `id` = 50;                      -- Sala Reunión
UPDATE `mrbs_room` SET `capacity` = 16, `description` = 'Tipo: Normal | A/C, TV' WHERE `id` = 51;                      -- Sala de Trabajo
UPDATE `mrbs_room` SET `room_name` = 'Hall Principal', `sort_key` = 'Hall Principal', `capacity` = 12, `description` = 'Tipo: Normal | A/C' WHERE `id` = 52;

-- --- Hall (area_id = 18) ---
UPDATE `mrbs_room` SET `capacity` = 70, `description` = 'Tipo: Normal | Sin equipamiento multimedia' WHERE `id` = 58;  -- Hall Principal (Ingreso)
UPDATE `mrbs_room` SET `capacity` = 80, `description` = 'Tipo: Normal | Sin equipamiento multimedia' WHERE `id` = 59;  -- Hall Edificio de Aulas
UPDATE `mrbs_room` SET `capacity` = 30, `description` = 'Tipo: Normal | Sin equipamiento multimedia' WHERE `id` = 62;  -- Hall Principal (Tortugas)


-- ============================================================================
-- 2. CREACIÓN DE NUEVA ÁREA: EDIFICIO DE LABORATORIOS (id = 21)
-- ============================================================================
INSERT INTO `mrbs_area` (
  `id`, `disabled`, `area_name`, `sort_key`, `timezone`, `area_admin_email`,
  `resolution`, `default_duration`, `default_duration_all_day`,
  `morningstarts`, `morningstarts_minutes`, `eveningends`, `eveningends_minutes`,
  `private_enabled`, `private_default`, `private_mandatory`, `private_override`,
  `min_create_ahead_enabled`, `min_create_ahead_secs`, `max_create_ahead_enabled`, `max_create_ahead_secs`,
  `min_delete_ahead_enabled`, `min_delete_ahead_secs`, `max_delete_ahead_enabled`, `max_delete_ahead_secs`,
  `max_per_day_enabled`, `max_per_day`, `max_per_week_enabled`, `max_per_week`,
  `max_per_month_enabled`, `max_per_month`, `max_per_year_enabled`, `max_per_year`,
  `max_per_future_enabled`, `max_per_future`, `max_secs_per_day_enabled`, `max_secs_per_day`,
  `max_secs_per_week_enabled`, `max_secs_per_week`, `max_secs_per_month_enabled`, `max_secs_per_month`,
  `max_secs_per_year_enabled`, `max_secs_per_year`, `max_secs_per_future_enabled`, `max_secs_per_future`,
  `max_duration_enabled`, `max_duration_secs`, `max_duration_periods`, `custom_html`,
  `approval_enabled`, `reminders_enabled`, `enable_periods`, `periods`,
  `confirmation_enabled`, `confirmed_default`, `times_along_top`, `default_type`
)
SELECT
  21, 0, 'Edificio de Laboratorios', 'Edificio de Laboratorios', 'America/Santiago', '',
  1800, 3600, 0,
  8, 0, 20, 0,
  0, 0, 0, 'none',
  0, 0, 0, 604800,
  0, 0, 0, 604800,
  0, 1, 0, 5,
  0, 10, 0, 50,
  0, 100, 0, 7200,
  0, 36000, 0, 90000,
  0, 360000, 0, 360000,
  0, 7200, 2, NULL,
  0, 1, 1, '["08:15 - 09:00","09:00 - 09:45","09:50 - 10:35","10:35 - 11:20","11:25 - 12:10","12:10 - 12:55","13:00 - 13:45","13:45 - 14:30","14:35 - 15:20","15:20 - 16:05","16:10 - 16:55","16:55 - 17:40","17:45 - 18:30","18:30 – 19:15"]',
  1, 1, 0, 'I'
WHERE NOT EXISTS (SELECT 1 FROM `mrbs_area` WHERE `id` = 21 OR `area_name` = 'Edificio de Laboratorios');


-- ============================================================================
-- 3. INCORPORACIÓN DE LAS 6 NUEVAS SALAS Y LABORATORIOS
-- ============================================================================

-- 3.1 Laboratorio Música (Edificio de Artes, area_id = 4)
INSERT INTO `mrbs_room` (`id`, `disabled`, `area_id`, `room_name`, `sort_key`, `description`, `capacity`, `room_admin_email`, `invalid_types`, `custom_html`)
SELECT 63, 0, 4, 'Laboratorio Música', 'Laboratorio Música', 'Tipo: Normal | Proyector, A/C', 20, '', '[]', NULL
WHERE NOT EXISTS (SELECT 1 FROM `mrbs_room` WHERE `id` = 63 OR (`area_id` = 4 AND `room_name` = 'Laboratorio Música'));

-- 3.2 Lab. de Artes (Edificio de Artes, area_id = 4)
INSERT INTO `mrbs_room` (`id`, `disabled`, `area_id`, `room_name`, `sort_key`, `description`, `capacity`, `room_admin_email`, `invalid_types`, `custom_html`)
SELECT 64, 0, 4, 'Lab. de Artes', 'Lab. de Artes', 'Tipo: Normal | Pantalla Táctil, A/C, TV', 25, '', '[]', NULL
WHERE NOT EXISTS (SELECT 1 FROM `mrbs_room` WHERE `id` = 64 OR (`area_id` = 4 AND `room_name` = 'Lab. de Artes'));

-- 3.3 Lab. Geomática (Pasillo Central, area_id = 3)
INSERT INTO `mrbs_room` (`id`, `disabled`, `area_id`, `room_name`, `sort_key`, `description`, `capacity`, `room_admin_email`, `invalid_types`, `custom_html`)
SELECT 65, 0, 3, 'Lab. Geomática', 'Lab. Geomática', 'Tipo: Híbrida | Proyector, PC, Audio AUX, Audio BT, A/C', 30, '', '[]', NULL
WHERE NOT EXISTS (SELECT 1 FROM `mrbs_room` WHERE `id` = 65 OR (`area_id` = 3 AND `room_name` = 'Lab. Geomática'));

-- 3.4 Lab 1 (Edificio de Laboratorios, area_id = 21)
INSERT INTO `mrbs_room` (`id`, `disabled`, `area_id`, `room_name`, `sort_key`, `description`, `capacity`, `room_admin_email`, `invalid_types`, `custom_html`)
SELECT 66, 0, 21, 'Lab 1', 'Lab 1', 'Tipo: Normal | A/C', 12, '', '[]', NULL
WHERE NOT EXISTS (SELECT 1 FROM `mrbs_room` WHERE `id` = 66 OR (`area_id` = 21 AND `room_name` = 'Lab 1'));

-- 3.5 Lab 2 (Edificio de Laboratorios, area_id = 21)
INSERT INTO `mrbs_room` (`id`, `disabled`, `area_id`, `room_name`, `sort_key`, `description`, `capacity`, `room_admin_email`, `invalid_types`, `custom_html`)
SELECT 67, 0, 21, 'Lab 2', 'Lab 2', 'Tipo: Normal | A/C, TV', 16, '', '[]', NULL
WHERE NOT EXISTS (SELECT 1 FROM `mrbs_room` WHERE `id` = 67 OR (`area_id` = 21 AND `room_name` = 'Lab 2'));

-- 3.6 Lab 3 (Edificio de Laboratorios, area_id = 21)
INSERT INTO `mrbs_room` (`id`, `disabled`, `area_id`, `room_name`, `sort_key`, `description`, `capacity`, `room_admin_email`, `invalid_types`, `custom_html`)
SELECT 68, 0, 21, 'Lab 3', 'Lab 3', 'Tipo: Normal | A/C, TV', 6, '', '[]', NULL
WHERE NOT EXISTS (SELECT 1 FROM `mrbs_room` WHERE `id` = 68 OR (`area_id` = 21 AND `room_name` = 'Lab 3'));

-- 4. Ajustar AUTO_INCREMENT para nuevas áreas y salas futuras
ALTER TABLE `mrbs_area` AUTO_INCREMENT = 22;
ALTER TABLE `mrbs_room` AUTO_INCREMENT = 69;

COMMIT;
