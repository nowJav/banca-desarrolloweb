<?php
declare(strict_types=1);

namespace App\models;

use App\core\Model;
use PDO;
use PDOException;

class Usuario extends Model
{
    public function registrarCliente(string $numeroCuenta, string $dpi, string $email, string $passwordHash): array
    {
        try {
            $stmt = $this->db->prepare('CALL sp_registrar_usuario_cliente(:numero_cuenta, :dpi, :email, :password_hash)');
            $stmt->execute([
                ':numero_cuenta' => $numeroCuenta,
                ':dpi' => $dpi,
                ':email' => $email,
                ':password_hash' => $passwordHash,
            ]);
            // Este SP no retorna fila; si no lanzó excepción, consideramos éxito
            while ($stmt->nextRowset()) { /* flush */ }
            return ['ok' => true, 'message' => 'Registro exitoso'];
        } catch (PDOException $e) {
            error_log('registrarCliente error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Error del servidor'];
        }
    }
}
