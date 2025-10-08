<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;

class RegistroController extends Controller
{
    // GET /registro
    // SP: no aplica (muestra formulario)
    public function showForm(): void
    {
        $this->render('registro/form');
    }

    // POST /registro
    // SP: sp_registrar_usuario_cliente
    public function register(): void
    {
        $this->render('registro/form');
    }
}

