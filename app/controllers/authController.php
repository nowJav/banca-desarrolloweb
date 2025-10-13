<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;
use App\core\Auth;
use App\core\Csrf;
use App\services\SeguridadService;

class AuthController extends Controller
{
    // GET /login
    public function showLogin(): void
    {
        $this->render('auth/login');
    }

    // POST /login
    public function login(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'login')) {
            $this->setFlash('error', 'CSRF inválido. Intenta nuevamente.');
            header('Location: /login');
            exit;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->setFlash('error', 'Correo y contraseña son requeridos.');
            header('Location: /login');
            exit;
        }

        $sec = new SeguridadService();
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        if ($sec->isLoginBlocked($email, $ip)) {
            $this->setFlash('error', 'Demasiados intentos fallidos. Intenta más tarde.');
            header('Location: /login');
            exit;
        }

        $auth = new Auth();
        if ($auth->login($email, $password)) {
            $sec->recordLoginAttempt($email, true, $ip);
            $this->setFlash('success', 'Bienvenido. Has iniciado sesión.');
            $role = $_SESSION['user_role'] ?? '';
            $dest = '/';
            if ($role === 'admin') { $dest = '/admin'; }
            elseif ($role === 'cajero') { $dest = '/cajero/crear-cuenta'; }
            elseif ($role === 'cliente') { $dest = '/cliente/terceros'; }
            header('Location: ' . $dest);
            exit;
        }

        $sec->recordLoginAttempt($email, false, $ip);
        $this->setFlash('error', 'Credenciales inválidas.');
        header('Location: /login');
        exit;
    }

    // POST /logout
    public function logout(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'logout')) {
            $this->setFlash('error', 'CSRF inválido.');
            header('Location: /');
            exit;
        }
        $auth = new Auth();
        $auth->logout();
        $this->setFlash('success', 'Sesión cerrada correctamente.');
        header('Location: /login');
        exit;
    }
}

