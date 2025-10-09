<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;
use App\core\Auth;
use App\core\Csrf;

class AuthController extends Controller
{
    // GET /login
    // SP: no aplica (solo muestra formulario)
    public function showLogin(): void
    {
        $this->render('auth/login');
    }

    // POST /login
    // SP: no usa SP; valida usuario y password_hash en tabla usuarios
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

        $auth = new Auth();
        if ($auth->login($email, $password)) {
            $this->setFlash('success', 'Bienvenido. Has iniciado sesión.');
            $role = $_SESSION['user_role'] ?? '';
            $dest = '/';
            if ($role === 'admin') { $dest = '/admin'; }
            elseif ($role === 'cajero') { $dest = '/cajero/crear-cuenta'; }
            elseif ($role === 'cliente') { $dest = '/cliente/terceros'; }
            header('Location: ' . $dest);
            exit;
        }

        $this->setFlash('error', 'Credenciales inválidas.');
        header('Location: /login');
        exit;
    }

    // POST /logout
    // SP: no aplica; cierra sesión y registra auditoría
    public function logout(): void
    {
        $auth = new Auth();
        $auth->logout();
        $this->setFlash('success', 'Sesión cerrada correctamente.');
        header('Location: /login');
        exit;
    }
}
