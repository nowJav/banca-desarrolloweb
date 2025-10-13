<?php
declare(strict_types=1);

namespace App\services;

use App\config\Database;
use PDO;

class SeguridadService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function isLoginBlocked(string $email, ?string $ip = null): bool
    {
        $ip = $ip ?: ($_SERVER['REMOTE_ADDR'] ?? null);
        $windowSeconds = (int)($_ENV['LOGIN_RATE_WINDOW'] ?? 900); // 15 min
        $maxAttempts   = (int)($_ENV['LOGIN_RATE_MAX'] ?? 5);

        $sql = 'SELECT COUNT(*) AS fail_count
                FROM intentos_login
                WHERE usuario_email = :email
                  AND (ip = :ip OR :ip IS NULL)
                  AND exito = 0
                  AND creado_at > (NOW() - INTERVAL :win SECOND)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':win', $windowSeconds, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['fail_count' => 0];
        return ((int)$row['fail_count']) >= $maxAttempts;
    }

    public function recordLoginAttempt(string $email, bool $success, ?string $ip = null): void
    {
        $ip = $ip ?: ($_SERVER['REMOTE_ADDR'] ?? null);
        $stmt = $this->db->prepare('INSERT INTO intentos_login (usuario_email, exito, ip) VALUES (:email, :exito, :ip)');
        $stmt->execute([':email' => $email, ':exito' => $success ? 1 : 0, ':ip' => $ip]);
    }
}
