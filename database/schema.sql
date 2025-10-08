-- Recomendado al inicio del script
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET sql_notes = 0;
SET FOREIGN_KEY_CHECKS = 0;

-- Limpieza (opcional en desarrollo)
DROP VIEW IF EXISTS vw_estado_cuenta;
DROP VIEW IF EXISTS vw_kpis_dia;

DROP TABLE IF EXISTS auditoria_eventos;
DROP TABLE IF EXISTS intentos_login;
DROP TABLE IF EXISTS sesiones_activas;
DROP TABLE IF EXISTS terceros_resumen_diario;
DROP TABLE IF EXISTS movimientos;
DROP TABLE IF EXISTS transferencias;
DROP TABLE IF EXISTS terceros;
DROP TABLE IF EXISTS cuentas;
DROP TABLE IF EXISTS cajeros;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS parametros_sistema;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- ROLES
CREATE TABLE roles (
  id      TINYINT PRIMARY KEY,
  nombre  VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (id,nombre) VALUES (1,'admin'),(2,'cajero'),(3,'cliente');

-- USUARIOS
CREATE TABLE usuarios (
  id              BIGINT PRIMARY KEY AUTO_INCREMENT,
  email           VARCHAR(160) NOT NULL UNIQUE,
  pass_hash       VARCHAR(255) NOT NULL,
  role_id         TINYINT NOT NULL,
  activo          BOOLEAN NOT NULL DEFAULT TRUE,
  ultimo_login_at DATETIME NULL,
  creado_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuarios_roles FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CLIENTES
CREATE TABLE clientes (
  id            BIGINT PRIMARY KEY AUTO_INCREMENT,
  usuario_id    BIGINT UNIQUE,
  dpi           VARCHAR(20) NOT NULL UNIQUE,
  nombre        VARCHAR(140) NOT NULL,
  telefono      VARCHAR(30),
  creado_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_clientes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CAJEROS
CREATE TABLE cajeros (
  id            BIGINT PRIMARY KEY AUTO_INCREMENT,
  usuario_id    BIGINT NOT NULL UNIQUE,
  nombre        VARCHAR(140) NOT NULL,
  activo        BOOLEAN NOT NULL DEFAULT TRUE,
  creado_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cajeros_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CUENTAS
CREATE TABLE cuentas (
  id              BIGINT PRIMARY KEY AUTO_INCREMENT,
  numero_cuenta   VARCHAR(24) NOT NULL UNIQUE,
  cliente_id      BIGINT NOT NULL,
  saldo           DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  estado          ENUM('activa','bloqueada','cerrada') NOT NULL DEFAULT 'activa',
  creado_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cuentas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_cuentas_cliente ON cuentas(cliente_id);

-- TERCEROS (beneficiarios por usuario)
CREATE TABLE terceros (
  id                  BIGINT PRIMARY KEY AUTO_INCREMENT,
  usuario_owner_id    BIGINT NOT NULL,
  cuenta_tercero_id   BIGINT NOT NULL,
  alias               VARCHAR(80) NOT NULL,
  monto_max_op        DECIMAL(14,2) NOT NULL CHECK (monto_max_op > 0),
  max_tx_diarias      INT NOT NULL CHECK (max_tx_diarias > 0),
  activo              BOOLEAN NOT NULL DEFAULT TRUE,
  creado_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (usuario_owner_id, cuenta_tercero_id),
  CONSTRAINT fk_ter_user FOREIGN KEY (usuario_owner_id) REFERENCES usuarios(id),
  CONSTRAINT fk_ter_cuenta FOREIGN KEY (cuenta_tercero_id) REFERENCES cuentas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TRANSFERENCIAS (cabecera)
CREATE TABLE transferencias (
  id_tx            CHAR(36) PRIMARY KEY,
  cuenta_origen_id BIGINT NOT NULL,
  cuenta_dest_id   BIGINT NOT NULL,
  monto            DECIMAL(14,2) NOT NULL CHECK (monto > 0),
  estado           ENUM('pendiente','completada','fallida') NOT NULL DEFAULT 'pendiente',
  creado_por       BIGINT NOT NULL,
  creado_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tx_origen FOREIGN KEY (cuenta_origen_id) REFERENCES cuentas(id),
  CONSTRAINT fk_tx_dest   FOREIGN KEY (cuenta_dest_id)  REFERENCES cuentas(id),
  CONSTRAINT fk_tx_user   FOREIGN KEY (creado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_tx_origen_fecha ON transferencias(cuenta_origen_id, creado_at);
CREATE INDEX idx_tx_dest_fecha   ON transferencias(cuenta_dest_id, creado_at);

-- MOVIMIENTOS (detalle)
CREATE TABLE movimientos (
  id             BIGINT PRIMARY KEY AUTO_INCREMENT,
  cuenta_id      BIGINT NOT NULL,
  tipo           ENUM('deposito','retiro','transf_out','transf_in') NOT NULL,
  monto          DECIMAL(14,2) NOT NULL CHECK (monto >= 0),
  id_tx          CHAR(36) NULL,
  glosa          VARCHAR(200),
  creado_por     BIGINT NULL,
  creado_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mov_cuenta FOREIGN KEY (cuenta_id) REFERENCES cuentas(id),
  CONSTRAINT fk_mov_user   FOREIGN KEY (creado_por) REFERENCES usuarios(id),
  CONSTRAINT fk_mov_tx     FOREIGN KEY (id_tx) REFERENCES transferencias(id_tx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_mov_cuenta_fecha ON movimientos(cuenta_id, creado_at);
CREATE INDEX idx_mov_tipo_fecha   ON movimientos(tipo, creado_at);

-- PARÁMETROS
CREATE TABLE parametros_sistema (
  clave          VARCHAR(60) PRIMARY KEY,
  valor          VARCHAR(255) NOT NULL,
  actualizado_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SESIONES EN BD (auditoría fina)
CREATE TABLE sesiones_activas (
  session_id   VARCHAR(128) PRIMARY KEY,
  usuario_id   BIGINT NULL,
  ip           VARCHAR(45),
  user_agent   VARCHAR(255),
  data         MEDIUMBLOB,
  last_activity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sesiones_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- RATE LIMIT LOGIN
CREATE TABLE intentos_login (
  id             BIGINT PRIMARY KEY AUTO_INCREMENT,
  usuario_email  VARCHAR(160) NOT NULL,
  exito          BOOLEAN NOT NULL,
  ip             VARCHAR(45),
  creado_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- RESUMENES DIARIOS POR TERCERO (para topes y KPIs rápidos)
CREATE TABLE terceros_resumen_diario (
  id                 BIGINT PRIMARY KEY AUTO_INCREMENT,
  tercero_id         BIGINT NOT NULL,
  fecha              DATE NOT NULL,
  conteo             INT NOT NULL DEFAULT 0,
  monto_acumulado    DECIMAL(14,2) NOT NULL DEFAULT 0,
  UNIQUE (tercero_id, fecha),
  CONSTRAINT fk_res_tercero FOREIGN KEY (tercero_id) REFERENCES terceros(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AUDITORÍA
CREATE TABLE auditoria_eventos (
  id           BIGINT PRIMARY KEY AUTO_INCREMENT,
  usuario_id   BIGINT NULL,
  entidad      VARCHAR(60) NOT NULL,
  entidad_id   VARCHAR(60) NOT NULL,
  accion       VARCHAR(40) NOT NULL,
  datos_previos JSON NULL,
  datos_nuevos  JSON NULL,
  ip           VARCHAR(45),
  creado_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_auditoria_user FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- VISTAS
CREATE OR REPLACE VIEW vw_estado_cuenta AS
SELECT
  c.id           AS cuenta_id,
  c.numero_cuenta,
  m.creado_at,
  m.tipo,
  CASE WHEN m.tipo IN ('deposito','transf_in') THEN m.monto ELSE 0 END AS credito,
  CASE WHEN m.tipo IN ('retiro','transf_out') THEN m.monto ELSE 0 END AS debito,
  m.id_tx,
  m.glosa
FROM cuentas c
JOIN movimientos m ON m.cuenta_id = c.id;

CREATE OR REPLACE VIEW vw_kpis_dia AS
SELECT
  DATE(NOW()) AS fecha,
  (SELECT COUNT(*) FROM cuentas  WHERE DATE(creado_at)=DATE(NOW())) AS cuentas_creadas,
  (SELECT COUNT(*) FROM clientes WHERE DATE(creado_at)=DATE(NOW())) AS clientes_registrados,
  (SELECT COUNT(*) FROM movimientos WHERE DATE(creado_at)=DATE(NOW())) AS transacciones,
  (SELECT COUNT(*) FROM movimientos WHERE tipo='deposito' AND DATE(creado_at)=DATE(NOW())) AS depositos,
  (SELECT COUNT(*) FROM movimientos WHERE tipo='retiro'    AND DATE(creado_at)=DATE(NOW())) AS retiros,
  (SELECT IFNULL(SUM(monto),0) FROM movimientos WHERE tipo='deposito' AND DATE(creado_at)=DATE(NOW())) AS monto_depositos,
  (SELECT IFNULL(SUM(monto),0) FROM movimientos WHERE tipo='retiro'    AND DATE(creado_at)=DATE(NOW())) AS monto_retiros;

SET sql_notes = 1;
