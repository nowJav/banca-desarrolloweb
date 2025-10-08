<?php
declare(strict_types=1);

namespace App\core;

use SessionHandlerInterface;
use PDO;

class DbSessionHandler implements SessionHandlerInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \App\config\Database::getConnection();
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string
    {
        $stmt = $this->db->prepare('SELECT data FROM sesiones_activas WHERE session_id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['data'] ?? '';
    }

    public function write($id, $data): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $sql = 'INSERT INTO sesiones_activas (session_id, data, ip, user_agent, last_activity, usuario_id)
                VALUES (:id, :data, :ip, :ua, NOW(), :uid)
                ON DUPLICATE KEY UPDATE data = VALUES(data), ip = VALUES(ip), user_agent = VALUES(user_agent), last_activity = VALUES(last_activity), usuario_id = COALESCE(VALUES(usuario_id), usuario_id)';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':data' => $data, ':ip' => $ip, ':ua' => $ua, ':uid' => $uid]);
    }

    public function destroy($id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM sesiones_activas WHERE session_id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function gc($max_lifetime): int|false
    {
        $stmt = $this->db->prepare('DELETE FROM sesiones_activas WHERE last_activity < (NOW() - INTERVAL :seconds SECOND)');
        $stmt->bindValue(':seconds', (int)$max_lifetime, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}

