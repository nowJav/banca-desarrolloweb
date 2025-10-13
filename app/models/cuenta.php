<?php
declare(strict_types=1);

namespace App\models;

use App\core\Model;
use PDO;
use PDOException;

class Cuenta extends Model
{
    public function crearCuenta(int $clienteId, string $tipo, float $saldoInicial): array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_crear_cuenta(:cliente_id, :tipo, :saldo_inicial)');
            $stmt->execute([
                ':cliente_id' => $clienteId,
                ':tipo' => $tipo,
                ':saldo_inicial' => $saldoInicial,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $ok = (bool)($row['ok'] ?? $row['success'] ?? $row['estado'] ?? ($row ? true : false));
            $message = (string)($row['message'] ?? $row['mensaje'] ?? ($ok ? 'Cuenta creada' : 'Error al crear cuenta'));
            $id = $row['cuenta_id'] ?? $row['id'] ?? null;
            while ($stmt->nextRowset()) { /* flush */ }
            return ['ok' => $ok, 'message' => $message, 'id' => $id];
        } catch (PDOException $e) {
            error_log('crearCuenta error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Error del servidor'];
        }
    }

    public function bloquearCuenta(int $cuentaId): array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_bloquear_cuenta(:cuenta_id)');
            $stmt->execute([':cuenta_id' => $cuentaId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $ok = (bool)($row['ok'] ?? $row['success'] ?? $row['estado'] ?? ($row ? true : false));
            $message = (string)($row['message'] ?? $row['mensaje'] ?? ($ok ? 'Cuenta bloqueada' : 'Error al bloquear'));
            while ($stmt->nextRowset()) { /* flush */ }
            return ['ok' => $ok, 'message' => $message];
        } catch (PDOException $e) {
            error_log('bloquearCuenta error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Error del servidor'];
        }
    }

    public function desbloquearCuenta(int $cuentaId): array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_desbloquear_cuenta(:cuenta_id)');
            $stmt->execute([':cuenta_id' => $cuentaId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $ok = (bool)($row['ok'] ?? $row['success'] ?? $row['estado'] ?? ($row ? true : false));
            $message = (string)($row['message'] ?? $row['mensaje'] ?? ($ok ? 'Cuenta desbloqueada' : 'Error al desbloquear'));
            while ($stmt->nextRowset()) { /* flush */ }
            return ['ok' => $ok, 'message' => $message];
        } catch (PDOException $e) {
            error_log('desbloquearCuenta error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Error del servidor'];
        }
    }
}
