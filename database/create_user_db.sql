-- === Crear usuario de aplicación (solo localhost) ===
CREATE USER IF NOT EXISTS 'app_banca'@'localhost'
IDENTIFIED BY 'banca_desarrolloweb_pswd';

-- Privilegios mínimos para operar la app:
--  - SELECT/INSERT/UPDATE/DELETE: CRUD en tablas
--  - EXECUTE: ejecutar Stored Procedures
--  - SHOW VIEW: consultar vistas (vw_estado_cuenta, vw_kpis_dia)
GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE, SHOW VIEW
ON `banca_desarrolloweb`.*
TO 'app_banca'@'localhost';

-- (Opcional) si tu PHP conectará desde 127.0.0.1 explícitamente:
-- CREATE USER IF NOT EXISTS 'app_banca'@'127.0.0.1' IDENTIFIED BY 'TU_PASSWORD_SUPER_SEGURO';
-- GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE, SHOW VIEW ON `banca_desarrolloweb`.* TO 'app_banca'@'127.0.0.1';

-- (Opcional) si en algún momento quisieras permitir acceso desde cualquier host (no recomendado en local):
-- CREATE USER IF NOT EXISTS 'app_banca'@'%' IDENTIFIED BY 'TU_PASSWORD_SUPER_SEGURO';
-- GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE, SHOW VIEW ON `banca_desarrolloweb`.* TO 'app_banca'@'%';

FLUSH PRIVILEGES;

-- === Verificación rápida (ejecuta estas líneas para confirmar) ===
-- SHOW GRANTS FOR 'app_banca'@'localhost';
-- USE banca_desarrolloweb;
-- CALL sp_kpis_dia();   -- deberías obtener el registro de KPIs del día
