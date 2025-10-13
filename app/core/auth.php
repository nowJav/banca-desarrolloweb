<?php

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
        $stmt = $this->db->prepare('SELECT id, email, pass_hash AS password_hash, role_id FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, (string)$user['password_hash'])) {
            $this->audit('login_failed', 'session', session_id() ?: null, ['ok' => false]);
            return false;
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_email'] = (string)$user['email'];
        $_SESSION['user_role'] = isset($user['role_id']) ? (string)$user['role_id'] : ($_SESSION['user_role'] ?? null);

        $this->attachUserToSession((int)$user['id']);
        $this->audit('login', 'session', session_id() ?: null, ['ok' => true]);
        return true;
    }

    public function logout(): void
    {
        $this->audit('logout', 'session', session_id() ?: null, ['ok' => true]);

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

    private function audit(string $accion, string $entidad, $entidadId, array $datosNuevos = []): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $usuarioId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
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

