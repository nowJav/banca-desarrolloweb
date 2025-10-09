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
            return ['ok' => false, 'message' => $e->getMessage()];
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
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function estadoCuenta(int $clienteId, ?string $desde = null, ?string $hasta = null): array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_listado_estado_cuenta(:cliente_id, :desde, :hasta)');
            $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmt->bindValue(':desde', $desde);
            $stmt->bindValue(':hasta', $hasta);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            while ($stmt->nextRowset()) { /* flush */ }
            return $rows;
        } catch (PDOException $e) {
            return [];
        }
    }
}
