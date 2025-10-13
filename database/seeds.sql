-- Admin y cajero (hashes de ejemplo; reemplaza por reales en app)
INSERT INTO usuarios (email, pass_hash, role_id, activo) VALUES
('admin@demo.com',  '$2y$10$.tPHYuSCiCYclwXor2lgqOTpKPtPPV8Fx577RNTny1xLS2t5JFPCe', 1, TRUE),
('cajero@demo.com', '$2y$10$.tPHYuSCiCYclwXor2lgqOTpKPtPPV8Fx577RNTny1xLS2t5JFPCe', 2, TRUE);
INSERT INTO cajeros (usuario_id, nombre, activo)
SELECT id, 'Cajero Demo', TRUE FROM usuarios WHERE email='cajero@demo.com';

-- Clientes sin usuario (aún)
INSERT INTO clientes (dpi, nombre, telefono) VALUES
('1000000000101','Ana Cliente','555-0001'),
('1000000000202','Ben Cliente','555-0002');

-- Cuentas
INSERT INTO cuentas (numero_cuenta, cliente_id, saldo, estado, creado_at) VALUES
('110-000-0001', (SELECT id FROM clientes WHERE dpi='1000000000101'), 2500.00, 'activa', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('110-000-0002', (SELECT id FROM clientes WHERE dpi='1000000000202'), 1800.00, 'activa', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Movimientos históricos (para KPIs)
INSERT INTO movimientos (cuenta_id, tipo, monto, glosa, creado_por, creado_at)
SELECT c.id, 'deposito', 500.00, 'Seed depósito', (SELECT id FROM usuarios WHERE email='cajero@demo.com'), DATE_SUB(NOW(), INTERVAL 2 DAY)
FROM cuentas c WHERE c.numero_cuenta='110-000-0001';

INSERT INTO movimientos (cuenta_id, tipo, monto, glosa, creado_por, creado_at)
SELECT c.id, 'retiro', 200.00, 'Seed retiro', (SELECT id FROM usuarios WHERE email='cajero@demo.com'), DATE_SUB(NOW(), INTERVAL 1 DAY)
FROM cuentas c WHERE c.numero_cuenta='110-000-0002';

-- Registrar usuario cliente para cuenta 0001 (vincula email a cuenta)
-- (Normalmente lo haría sp_registrar_usuario_cliente; aquí directo por seed)
INSERT INTO usuarios (email, pass_hash, role_id, activo)
VALUES ('ana@demo.com', '$2y$10$.tPHYuSCiCYclwXor2lgqOTpKPtPPV8Fx577RNTny1xLS2t5JFPCe', 3, TRUE);
UPDATE clientes SET usuario_id=(SELECT id FROM usuarios WHERE email='ana@demo.com')
WHERE dpi='1000000000101';

-- Terceros de Ana: agrega la cuenta 0002 como tercero con límites
INSERT INTO terceros (usuario_owner_id, cuenta_tercero_id, alias, monto_max_op, max_tx_diarias, activo)
VALUES (
  (SELECT id FROM usuarios WHERE email='ana@demo.com'),
  (SELECT id FROM cuentas  WHERE numero_cuenta='110-000-0002'),
  'Ben', 400.00, 3, TRUE
);

-- Transferencias simuladas de Ana -> Ben (varias en el día y días previos)
-- Día -1
SET @tx := REPLACE(UUID(),'-','-');
INSERT INTO transferencias (id_tx, cuenta_origen_id, cuenta_dest_id, monto, estado, creado_por, creado_at)
VALUES (
  @tx,
  (SELECT id FROM cuentas WHERE numero_cuenta='110-000-0001'),
  (SELECT id FROM cuentas WHERE numero_cuenta='110-000-0002'),
  150.00, 'completada',
  (SELECT id FROM usuarios WHERE email='ana@demo.com'),
  DATE_SUB(NOW(), INTERVAL 1 DAY)
);
INSERT INTO movimientos (cuenta_id, tipo, monto, id_tx, glosa, creado_por, creado_at)
VALUES
((SELECT id FROM cuentas WHERE numero_cuenta='110-000-0001'),'transf_out',150.00,@tx,'Seed transf', (SELECT id FROM usuarios WHERE email='ana@demo.com'), DATE_SUB(NOW(), INTERVAL 1 DAY)),
((SELECT id FROM cuentas WHERE numero_cuenta='110-000-0002'),'transf_in', 150.00,@tx,'Seed transf', (SELECT id FROM usuarios WHERE email='ana@demo.com'), DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Resumen diario (día -1): upsert
INSERT INTO terceros_resumen_diario (tercero_id, fecha, conteo, monto_acumulado)
VALUES (
  (SELECT id FROM terceros WHERE alias='Ben' LIMIT 1),
  DATE(DATE_SUB(NOW(), INTERVAL 1 DAY)),
  1, 150.00
)
ON DUPLICATE KEY UPDATE conteo=conteo+1, monto_acumulado=monto_acumulado+VALUES(monto_acumulado);

-- Día 0 (hoy) varias para probar tope (máx 3/día)
-- 1
CALL sp_transferir(
  (SELECT id FROM usuarios WHERE email='ana@demo.com'),
  '110-000-0001',
  '110-000-0002',
  120.00,
  @out_tx
);
-- 2
CALL sp_transferir(
  (SELECT id FROM usuarios WHERE email='ana@demo.com'),
  '110-000-0001',
  '110-000-0002',
  80.00,
  @out_tx
);
-- 3 (llega al tope diario = 3)
CALL sp_transferir(
  (SELECT id FROM usuarios WHERE email='ana@demo.com'),
  '110-000-0001',
  '110-000-0002',
  60.00,
  @out_tx
);

-- Esta cuarta debería fallar en la app si se intenta (tope diario superado)
