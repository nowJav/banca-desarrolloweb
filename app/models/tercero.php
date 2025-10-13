<?php
declare(strict_types=1);

namespace App\models;

use App\core\Model;
use PDO;
use PDOException;

class Tercero extends Model
{
    public function agregar(int $usuarioOwnerId, string $numeroCuentaDestino, string $alias, float $montoMaxOp, int $maxTxDiarias): array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_agregar_tercero(:owner_id, :num_dest, :alias, :monto_max, :max_diarias)');
            $stmt->execute([
                ':owner_id' => $usuarioOwnerId,
                ':num_dest' => $numeroCuentaDestino,
                ':alias' => $alias,
                ':monto_max' => $montoMaxOp,
                ':max_diarias' => $maxTxDiarias,
            ]);
            while ($stmt->nextRowset()) { /* flush */ }
            return ['ok' => true, 'message' => 'Tercero agregado'];
        } catch (PDOException $e) {
            error_log('tercero.agregar error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Error del servidor'];
        }
    }

    public function activar(int $terceroId, int $usuarioOwnerId, bool $activo): bool
    {
        $stmt = $this->db->prepare('UPDATE terceros SET activo = :activo WHERE id = :id AND usuario_owner_id = :uid');
        return $stmt->execute([':activo' => $activo ? 1 : 0, ':id' => $terceroId, ':uid' => $usuarioOwnerId]);
    }

    public function listarPorUsuario(int $usuarioOwnerId): array
    {
        $sql = 'SELECT t.id, t.alias, t.monto_max_op, t.max_tx_diarias, t.activo, c.numero_cuenta
                FROM terceros t JOIN cuentas c ON c.id = t.cuenta_tercero_id
                WHERE t.usuario_owner_id = :uid
                ORDER BY t.alias';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $usuarioOwnerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function resumenHoy(int $terceroId): array
    {
        $stmt = $this->db->prepare('SELECT conteo, monto_acumulado FROM terceros_resumen_diario WHERE tercero_id = :tid AND fecha = DATE(NOW())');
        $stmt->execute([':tid' => $terceroId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['conteo'=>0,'monto_acumulado'=>0];
    }
}
