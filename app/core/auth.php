<?php
declare(strict_types=1);

namespace App\core;

use PDO;

class Auth
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \App\config\Database::getConnection();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function login(string $email, string $password): bool
    {
        $stmt = $this->db->prepare('SELECT id, email, password_hash, rol FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, (string)$user['password_hash'])) {
            $this->audit('login_failed', 'Intento de login fallido', null);
            return false;
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_email'] = (string)$user['email'];
        $_SESSION['user_role'] = (string)$user['rol'];

        $this->attachUserToSession((int)$user['id']);
        $this->audit('login', 'Usuario autenticado', (int)$user['id']);
        return true;
    }

    public function logout(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $this->audit('logout', 'Usuario cierra sesión', $userId ? (int)$userId : null);

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
    }

    public function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public function authorize(string ...$roles): bool
    {
        $role = $this->role();
        return $role !== null && in_array($role, $roles, true);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    private function attachUserToSession(int $userId): void
    {
        $sid = session_id();
        if (!$sid) {
            return;
        }
        $sql = 'UPDATE sesiones_activas SET usuario_id = :uid WHERE session_id = :sid';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId, ':sid' => $sid]);
    }

    private function audit(string $tipo, string $descripcion, ?int $userId): void
    {
        $sid = session_id();
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $stmt = $this->db->prepare('INSERT INTO auditoria_eventos (tipo, descripcion, usuario_id, entidad, entidad_id, ip, user_agent, creado_en) VALUES (:tipo, :descripcion, :usuario_id, :entidad, :entidad_id, :ip, :ua, NOW())');
        $stmt->execute([
            ':tipo' => $tipo,
            ':descripcion' => $descripcion,
            ':usuario_id' => $userId,
            ':entidad' => 'session',
            ':entidad_id' => $sid ?: null,
            ':ip' => $ip,
            ':ua' => $ua,
        ]);
    }
}

