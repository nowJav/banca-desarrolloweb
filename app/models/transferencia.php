<?php
declare(strict_types=1);

namespace App\models;

use App\core\Model;
use PDO;
use PDOException;

class Transferencia extends Model
{
    public function transferir(int $clienteId, int $terceroId, float $monto, ?string $descripcion = null): array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_transferir(:cliente_id, :tercero_id, :monto, :descripcion)');
            $stmt->execute([
                ':cliente_id' => $clienteId,
                ':tercero_id' => $terceroId,
                ':monto' => $monto,
                ':descripcion' => $descripcion,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $ok = (bool)($row['ok'] ?? $row['success'] ?? $row['estado'] ?? ($row ? true : false));
            $message = (string)($row['message'] ?? $row['mensaje'] ?? ($ok ? 'Transferencia realizada' : 'Error en transferencia'));
            while ($stmt->nextRowset()) { /* flush */ }
            return ['ok' => $ok, 'message' => $message];
        } catch (PDOException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}
