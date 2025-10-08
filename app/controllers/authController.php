<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;

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
        // stub
        $this->render('auth/login');
    }

    // POST /logout
    // SP: no aplica; cierra sesión y registra auditoría
    public function logout(): void
    {
        // stub
        $this->render('auth/login');
    }
}

