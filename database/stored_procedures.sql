DELIMITER $$

-- Crear cuenta (crea cliente si hace falta) + depósito inicial opcional
CREATE PROCEDURE sp_crear_cuenta(
  IN p_numero_cuenta VARCHAR(24),
  IN p_dpi           VARCHAR(20),
  IN p_nombre        VARCHAR(140),
  IN p_monto_inicial DECIMAL(14,2),
  IN p_id_cajero     BIGINT
)
BEGIN
  DECLARE v_cliente_id BIGINT;

  START TRANSACTION;
    -- cliente por dpi (si no existe, lo creamos sin usuario)
    SELECT id INTO v_cliente_id FROM clientes WHERE dpi = p_dpi FOR UPDATE;
    IF v_cliente_id IS NULL THEN
      INSERT INTO clientes (dpi, nombre) VALUES (p_dpi, p_nombre);
      SET v_cliente_id = LAST_INSERT_ID();
    END IF;

    -- cuenta nueva
    INSERT INTO cuentas (numero_cuenta, cliente_id, saldo, estado)
    VALUES (p_numero_cuenta, v_cliente_id, 0.00, 'activa');

    -- depósito inicial
    IF p_monto_inicial IS NOT NULL AND p_monto_inicial > 0 THEN
      UPDATE cuentas SET saldo = saldo + p_monto_inicial
      WHERE numero_cuenta = p_numero_cuenta;

      INSERT INTO movimientos (cuenta_id, tipo, monto, glosa, creado_por)
      SELECT c.id, 'deposito', p_monto_inicial, 'Apertura', p_id_cajero
      FROM cuentas c WHERE c.numero_cuenta = p_numero_cuenta;
    END IF;
  COMMIT;
END$$

-- Registrar usuario cliente (1:1 cuenta-usuario, DPI debe coincidir)
CREATE PROCEDURE sp_registrar_usuario_cliente(
  IN p_numero_cuenta VARCHAR(24),
  IN p_dpi           VARCHAR(20),
  IN p_email         VARCHAR(160),
  IN p_pass_hash     VARCHAR(255)
)
BEGIN
  DECLARE v_cuenta_id BIGINT;
  DECLARE v_cliente_id BIGINT;

  -- validar cuenta y dpi
  SELECT c.id, c.cliente_id INTO v_cuenta_id, v_cliente_id
  FROM cuentas c JOIN clientes cl ON cl.id=c.cliente_id
  WHERE c.numero_cuenta=p_numero_cuenta AND cl.dpi=p_dpi;

  IF v_cuenta_id IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuenta o DPI inválidos';
  END IF;

  -- asegurar que el cliente no tenga usuario asignado
  IF (SELECT usuario_id FROM clientes WHERE id=v_cliente_id) IS NOT NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Ya existe usuario asignado a la cuenta';
  END IF;

  START TRANSACTION;
    INSERT INTO usuarios (email, pass_hash, role_id, activo)
    VALUES (p_email, p_pass_hash, 3, TRUE);

    UPDATE clientes
       SET usuario_id = LAST_INSERT_ID()
     WHERE id = v_cliente_id;
  COMMIT;
END$$

-- Depósito
CREATE PROCEDURE sp_deposito(
  IN p_numero_cuenta VARCHAR(24),
  IN p_monto         DECIMAL(14,2),
  IN p_id_cajero     BIGINT
)
BEGIN
  DECLARE v_cuenta_id BIGINT;

  START TRANSACTION;
    SELECT id INTO v_cuenta_id FROM cuentas
    WHERE numero_cuenta=p_numero_cuenta AND estado='activa' FOR UPDATE;
    IF v_cuenta_id IS NULL THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuenta no existe o no activa';
    END IF;

    UPDATE cuentas SET saldo = saldo + p_monto WHERE id=v_cuenta_id;

    INSERT INTO movimientos (cuenta_id, tipo, monto, glosa, creado_por)
    VALUES (v_cuenta_id, 'deposito', p_monto, 'Depósito en ventanilla', p_id_cajero);
  COMMIT;
END$$

-- Retiro
CREATE PROCEDURE sp_retiro(
  IN p_numero_cuenta VARCHAR(24),
  IN p_monto         DECIMAL(14,2),
  IN p_id_cajero     BIGINT
)
BEGIN
  DECLARE v_cuenta_id BIGINT;
  DECLARE v_saldo DECIMAL(14,2);

  START TRANSACTION;
    SELECT id, saldo INTO v_cuenta_id, v_saldo FROM cuentas
    WHERE numero_cuenta=p_numero_cuenta AND estado='activa' FOR UPDATE;
    IF v_cuenta_id IS NULL THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuenta no existe o no activa';
    END IF;
    IF v_saldo < p_monto THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Saldo insuficiente';
    END IF;

    UPDATE cuentas SET saldo = saldo - p_monto WHERE id=v_cuenta_id;

    INSERT INTO movimientos (cuenta_id, tipo, monto, glosa, creado_por)
    VALUES (v_cuenta_id, 'retiro', p_monto, 'Retiro en ventanilla', p_id_cajero);
  COMMIT;
END$$

-- Agregar tercero (beneficiario)
CREATE PROCEDURE sp_agregar_tercero(
  IN p_usuario_owner_id BIGINT,
  IN p_numero_cuenta_tercero VARCHAR(24),
  IN p_alias VARCHAR(80),
  IN p_monto_max_op DECIMAL(14,2),
  IN p_max_tx_diarias INT
)
BEGIN
  DECLARE v_cuenta_ter_id BIGINT;
  SELECT id INTO v_cuenta_ter_id FROM cuentas WHERE numero_cuenta=p_numero_cuenta_tercero;
  IF v_cuenta_ter_id IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuenta tercero inexistente';
  END IF;

  INSERT INTO terceros (usuario_owner_id, cuenta_tercero_id, alias, monto_max_op, max_tx_diarias, activo)
  VALUES (p_usuario_owner_id, v_cuenta_ter_id, p_alias, p_monto_max_op, p_max_tx_diarias, TRUE);
END$$

-- Transferir a tercero (con topes y resumen diario)
CREATE PROCEDURE sp_transferir(
  IN  p_usuario_id BIGINT,
  IN  p_numero_cuenta_origen VARCHAR(24),
  IN  p_numero_cuenta_destino VARCHAR(24),
  IN  p_monto DECIMAL(14,2),
  OUT p_id_tx CHAR(36)
)
BEGIN
  DECLARE v_cuenta_origen_id BIGINT;
  DECLARE v_cuenta_dest_id   BIGINT;
  DECLARE v_saldo_origen     DECIMAL(14,2);
  DECLARE v_tercero_id       BIGINT;
  DECLARE v_max_op           DECIMAL(14,2);
  DECLARE v_max_diarias      INT;
  DECLARE v_conteo_actual    INT DEFAULT 0;
  DECLARE v_monto_actual     DECIMAL(14,2) DEFAULT 0;
  DECLARE v_hoy DATE;

  SET v_hoy = DATE(NOW());
  SET p_id_tx = REPLACE(UUID(),'-','-'); -- UUID estándar

  -- Resolver cuentas
  SELECT id, saldo INTO v_cuenta_origen_id, v_saldo_origen
  FROM cuentas WHERE numero_cuenta=p_numero_cuenta_origen AND estado='activa' FOR UPDATE;
  IF v_cuenta_origen_id IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuenta origen inválida';
  END IF;

  SELECT id INTO v_cuenta_dest_id
  FROM cuentas WHERE numero_cuenta=p_numero_cuenta_destino AND estado='activa' FOR UPDATE;
  IF v_cuenta_dest_id IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuenta destino inválida';
  END IF;

  -- Validar que el destino esté registrado como tercero del usuario
  SELECT t.id, t.monto_max_op, t.max_tx_diarias
    INTO v_tercero_id, v_max_op, v_max_diarias
  FROM terceros t
  WHERE t.usuario_owner_id = p_usuario_id AND t.cuenta_tercero_id = v_cuenta_dest_id AND t.activo = TRUE
  FOR UPDATE;

  IF v_tercero_id IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Tercero no registrado o inactivo';
  END IF;

  IF p_monto > v_max_op THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Monto excede límite por operación';
  END IF;

  -- Resumen diario (conteo y monto acumulado)
  SELECT conteo, monto_acumulado INTO v_conteo_actual, v_monto_actual
  FROM terceros_resumen_diario
  WHERE tercero_id = v_tercero_id AND fecha = v_hoy
  FOR UPDATE;

  IF v_conteo_actual IS NULL THEN
    SET v_conteo_actual = 0;
    SET v_monto_actual  = 0;
  END IF;

  IF (v_conteo_actual + 1) > v_max_diarias THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Tope diario de transacciones excedido';
  END IF;

  IF v_saldo_origen < p_monto THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Saldo insuficiente';
  END IF;

  START TRANSACTION;
    -- Cabecera transferencia
    INSERT INTO transferencias (id_tx, cuenta_origen_id, cuenta_dest_id, monto, estado, creado_por)
    VALUES (p_id_tx, v_cuenta_origen_id, v_cuenta_dest_id, p_monto, 'pendiente', p_usuario_id);

    -- Débito / Crédito
    UPDATE cuentas SET saldo = saldo - p_monto WHERE id = v_cuenta_origen_id;
    UPDATE cuentas SET saldo = saldo + p_monto WHERE id = v_cuenta_dest_id;

    -- Movimientos con el mismo id_tx
    INSERT INTO movimientos (cuenta_id, tipo, monto, id_tx, glosa, creado_por)
    VALUES
      (v_cuenta_origen_id, 'transf_out', p_monto, p_id_tx, 'Transferencia a tercero', p_usuario_id),
      (v_cuenta_dest_id,   'transf_in',  p_monto, p_id_tx, 'Transferencia recibida', p_usuario_id);

    -- Upsert de resumen diario
    INSERT INTO terceros_resumen_diario (tercero_id, fecha, conteo, monto_acumulado)
    VALUES (v_tercero_id, v_hoy, 1, p_monto)
    ON DUPLICATE KEY UPDATE
      conteo = conteo + 1,
      monto_acumulado = monto_acumulado + VALUES(monto_acumulado);

    -- Marcar completada
    UPDATE transferencias SET estado='completada' WHERE id_tx = p_id_tx;
  COMMIT;
END$$

-- Estado de cuenta (rango)
CREATE PROCEDURE sp_listado_estado_cuenta(
  IN p_numero_cuenta VARCHAR(24),
  IN p_fecha_ini DATETIME,
  IN p_fecha_fin DATETIME
)
BEGIN
  SELECT * FROM vw_estado_cuenta
  WHERE numero_cuenta = p_numero_cuenta
    AND creado_at BETWEEN p_fecha_ini AND p_fecha_fin
  ORDER BY creado_at ASC;
END$$

-- KPIs del día
CREATE PROCEDURE sp_kpis_dia()
BEGIN
  SELECT * FROM vw_kpis_dia;
END$$

-- Utilidades de admin
CREATE PROCEDURE sp_bloquear_cuenta(IN p_numero_cuenta VARCHAR(24), IN p_admin_id BIGINT)
BEGIN
  UPDATE cuentas SET estado='bloqueada' WHERE numero_cuenta=p_numero_cuenta;
  INSERT INTO auditoria_eventos (usuario_id, entidad, entidad_id, accion)
  VALUES (p_admin_id, 'cuenta', p_numero_cuenta, 'bloquear');
END$$

CREATE PROCEDURE sp_desbloquear_cuenta(IN p_numero_cuenta VARCHAR(24), IN p_admin_id BIGINT)
BEGIN
  UPDATE cuentas SET estado='activa' WHERE numero_cuenta=p_numero_cuenta AND estado='bloqueada';
  INSERT INTO auditoria_eventos (usuario_id, entidad, entidad_id, accion)
  VALUES (p_admin_id, 'cuenta', p_numero_cuenta, 'desbloquear');
END$$

DELIMITER ;
