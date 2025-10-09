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

    public function registrar(string $tipo, string $descripcion, ?string $entidad = null, $entidadId = null): void
    {
        $usuarioId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $stmt = $this->db->prepare('INSERT INTO auditoria_eventos (tipo, descripcion, usuario_id, entidad, entidad_id, ip, user_agent, creado_en) VALUES (:tipo, :descripcion, :usuario_id, :entidad, :entidad_id, :ip, :ua, NOW())');
        $stmt->execute([
            ':tipo' => $tipo,
            ':descripcion' => $descripcion,
            ':usuario_id' => $usuarioId,
            ':entidad' => $entidad,
            ':entidad_id' => $entidadId,
            ':ip' => $ip,
            ':ua' => $ua,
        ]);
    }
}
