<?php
declare(strict_types=1);

namespace App\services;

use App\config\Database;
use PDO;

class AuditoriaService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function registrar(string $accion, string $entidad, $entidadId, array $datosNuevos = []): void
    {
        $usuarioId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $this->db->prepare('INSERT INTO auditoria_eventos (usuario_id, entidad, entidad_id, accion, datos_previos, datos_nuevos, ip, creado_at) VALUES (:usuario_id, :entidad, :entidad_id, :accion, :prev, :nuevos, :ip, NOW())');
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':entidad' => $entidad,
            ':entidad_id' => (string)$entidadId,
            ':accion' => $accion,
            ':prev' => null,
            ':nuevos' => json_encode($datosNuevos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':ip' => $ip,
        ]);
    }
}
