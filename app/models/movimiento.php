<?php
declare(strict_types=1);

namespace App\models;

use App\core\Model;
use PDO;
use PDOException;

class Movimiento extends Model
{
    public function deposito(string $cuenta, float $monto): array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_deposito(:cuenta, :monto)');
            $stmt->execute([':cuenta' => $cuenta, ':monto' => $monto]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $ok = (bool)($row['ok'] ?? $row['success'] ?? $row['estado'] ?? ($row ? true : false));
            $message = (string)($row['message'] ?? $row['mensaje'] ?? ($ok ? 'Depósito registrado' : 'Error en depósito'));
            while ($stmt->nextRowset()) { /* flush */ }
            return ['ok' => $ok, 'message' => $message];
        } catch (PDOException $e) {
            error_log('deposito error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Error del servidor'];
        }
    }

    public function retiro(string $cuenta, float $monto): array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_retiro(:cuenta, :monto)');
            $stmt->execute([':cuenta' => $cuenta, ':monto' => $monto]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $ok = (bool)($row['ok'] ?? $row['success'] ?? $row['estado'] ?? ($row ? true : false));
            $message = (string)($row['message'] ?? $row['mensaje'] ?? ($ok ? 'Retiro registrado' : 'Error en retiro'));
            while ($stmt->nextRowset()) { /* flush */ }
            return ['ok' => $ok, 'message' => $message];
        } catch (PDOException $e) {
            error_log('retiro error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Error del servidor'];
        }
    }

    public function estadoCuentaPorNumero(string $numeroCuenta, ?string $desde = null, ?string $hasta = null): array
    {
        try {
            $desde = $desde ? ($desde . ' 00:00:00') : date('Y-m-d 00:00:00');
            $hasta = $hasta ? ($hasta . ' 23:59:59') : date('Y-m-d 23:59:59');
            $stmt = $this->db->prepare('CALL sp_listado_estado_cuenta(:numero, :desde, :hasta)');
            $stmt->bindValue(':numero', $numeroCuenta);
            $stmt->bindValue(':desde', $desde);
            $stmt->bindValue(':hasta', $hasta);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            while ($stmt->nextRowset()) { /* flush */ }
            return $rows;
        } catch (PDOException $e) {
            error_log('estadoCuenta error: ' . $e->getMessage());
            return [];
        }
    }

    public function depositoCajero(string $numeroCuenta, float $monto, int $cajeroId, ?string $glosa = null): array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_deposito(:numero_cuenta, :monto, :cajero_id)');
            $stmt->execute([':numero_cuenta' => $numeroCuenta, ':monto' => $monto, ':cajero_id' => $cajeroId]);
            while ($stmt->nextRowset()) { /* flush */ }
            if ($glosa) {
                $this->anotarGlosaUltimoMovimiento($numeroCuenta, $glosa);
            }
            return ['ok' => true, 'message' => 'Depósito registrado'];
        } catch (PDOException $e) {
            error_log('depositoCajero error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Error del servidor'];
        }
    }

    public function retiroCajero(string $numeroCuenta, float $monto, int $cajeroId, ?string $glosa = null): array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_retiro(:numero_cuenta, :monto, :cajero_id)');
            $stmt->execute([':numero_cuenta' => $numeroCuenta, ':monto' => $monto, ':cajero_id' => $cajeroId]);
            while ($stmt->nextRowset()) { /* flush */ }
            if ($glosa) {
                $this->anotarGlosaUltimoMovimiento($numeroCuenta, $glosa);
            }
            return ['ok' => true, 'message' => 'Retiro registrado'];
        } catch (PDOException $e) {
            error_log('retiroCajero error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Error del servidor'];
        }
    }

    private function anotarGlosaUltimoMovimiento(string $numeroCuenta, string $glosa): void
    {
        // Actualiza la glosa del último movimiento de esa cuenta
        $sql = "UPDATE movimientos m
                JOIN cuentas c ON c.id = m.cuenta_id AND c.numero_cuenta = :num
                SET m.glosa = CASE WHEN (m.glosa IS NULL OR m.glosa='') THEN :glosa ELSE CONCAT(m.glosa, ' - ', :glosa) END
                WHERE m.id = (
                  SELECT id FROM movimientos m2 JOIN cuentas c2 ON c2.id=m2.cuenta_id AND c2.numero_cuenta=:num ORDER BY m2.id DESC LIMIT 1
                )";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':num' => $numeroCuenta, ':glosa' => $glosa]);
    }
}
