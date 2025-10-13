<?php
declare(strict_types=1);

namespace App\models;

use App\core\Model;
use PDO;
use PDOException;

class Transferencia extends Model
{
    public function transferir(int $usuarioId, string $numeroCuentaOrigen, string $numeroCuentaDestino, float $monto): array
    {
        try {
            // Usar variable de usuario para OUT param
            $this->db->query("SET @out_tx = NULL");
            $stmt = $this->db->prepare('CALL sp_transferir(:uid, :origen, :destino, :monto, @out_tx)');
            $stmt->execute([
                ':uid' => $usuarioId,
                ':origen' => $numeroCuentaOrigen,
                ':destino' => $numeroCuentaDestino,
                ':monto' => $monto,
            ]);
            // Recuperar OUT
            $tx = null;
            foreach ($this->db->query('SELECT @out_tx AS id_tx') as $row) { $tx = $row['id_tx'] ?? null; }
            while ($stmt->nextRowset()) { /* flush */ }
            return ['ok' => true, 'message' => 'Transferencia realizada', 'id_tx' => $tx];
        } catch (PDOException $e) {
            error_log('transferir error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Error del servidor'];
        }
    }
}
