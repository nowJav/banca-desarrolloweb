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
            // Obtener numero de cuenta para SP
            $num = $this->getNumeroCuenta($cuentaId);
            $adminId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $stmt = $this->db->prepare('CALL sp_bloquear_cuenta(:numero_cuenta, :admin_id)');
            $stmt->execute([':numero_cuenta' => $num, ':admin_id' => $adminId]);
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
            $num = $this->getNumeroCuenta($cuentaId);
            $adminId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $stmt = $this->db->prepare('CALL sp_desbloquear_cuenta(:numero_cuenta, :admin_id)');
            $stmt->execute([':numero_cuenta' => $num, ':admin_id' => $adminId]);
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

    public function buscarPorNumeroODpi(?string $numero, ?string $dpi): ?array
    {
        $where = [];
        $params = [];
        if ($numero) { $where[] = 'c.numero_cuenta = :num'; $params[':num'] = $numero; }
        if ($dpi) { $where[] = 'cl.dpi = :dpi'; $params[':dpi'] = $dpi; }
        if (empty($where)) { return null; }
        $sql = 'SELECT c.id, c.numero_cuenta, c.saldo, c.estado, cl.nombre, cl.dpi FROM cuentas c JOIN clientes cl ON cl.id=c.cliente_id WHERE ' . implode(' AND ', $where) . ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        return $row ?: null;
    }

    public function ultimosMovimientos(int $cuentaId, int $limit = 5): array
    {
        $stmt = $this->db->prepare('SELECT creado_at, tipo, monto, id_tx, glosa FROM movimientos WHERE cuenta_id = :cid ORDER BY creado_at DESC LIMIT :lim');
        $stmt->bindValue(':cid', $cuentaId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function getNumeroCuenta(int $cuentaId): string
    {
        $stmt = $this->db->prepare('SELECT numero_cuenta FROM cuentas WHERE id = :id');
        $stmt->execute([':id' => $cuentaId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (string)($row['numero_cuenta'] ?? '');
    }
}
