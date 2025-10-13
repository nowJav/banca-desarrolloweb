<?php
declare(strict_types=1);

namespace App\models;

use App\core\Model;
use PDO;
use PDOException;

class Usuario extends Model
{
    public function registrarCliente(string $nombre, string $email, string $passwordHash): array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_registrar_usuario_cliente(:nombre, :email, :password_hash)');
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':password_hash' => $passwordHash,
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            // Intents: SP might return first row with status/message/id
            $ok = (bool)($row['ok'] ?? $row['success'] ?? $row['estado'] ?? ($row ? true : false));
            $message = (string)($row['message'] ?? $row['mensaje'] ?? ($ok ? 'Registro exitoso' : 'Error en registro'));
            $id = $row['id'] ?? $row['cliente_id'] ?? null;
            // Ensure cursor closed to allow next queries
            while ($stmt->nextRowset()) { /* flush */ }
            return ['ok' => $ok, 'message' => $message, 'id' => $id];
        } catch (PDOException $e) {
            error_log('registrarCliente error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Error del servidor'];
        }
    }
}
