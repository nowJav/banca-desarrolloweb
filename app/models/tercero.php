<?php
declare(strict_types=1);

namespace App\models;

use App\core\Model;
use PDO;
use PDOException;

class Tercero extends Model
{
    public function agregar(int $clienteId, string $nombre, string $documento, string $banco, string $cuenta): array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_agregar_tercero(:cliente_id, :nombre, :documento, :banco, :cuenta)');
            $stmt->execute([
                ':cliente_id' => $clienteId,
                ':nombre' => $nombre,
                ':documento' => $documento,
                ':banco' => $banco,
                ':cuenta' => $cuenta,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $ok = (bool)($row['ok'] ?? $row['success'] ?? $row['estado'] ?? ($row ? true : false));
            $message = (string)($row['message'] ?? $row['mensaje'] ?? ($ok ? 'Tercero agregado' : 'Error al agregar tercero'));
            while ($stmt->nextRowset()) { /* flush */ }
            return ['ok' => $ok, 'message' => $message];
        } catch (PDOException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function activar(int $terceroId, int $clienteId, bool $activo): bool
    {
        $stmt = $this->db->prepare('UPDATE terceros SET activo = :activo WHERE id = :id AND cliente_id = :cliente_id');
        return $stmt->execute([':activo' => $activo ? 1 : 0, ':id' => $terceroId, ':cliente_id' => $clienteId]);
    }

    public function listarPorCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare('SELECT id, nombre, documento, banco, cuenta, activo FROM terceros WHERE cliente_id = :cid ORDER BY nombre');
        $stmt->execute([':cid' => $clienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
