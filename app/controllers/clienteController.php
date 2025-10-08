<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;

class ClienteController extends Controller
{
    // GET /cliente/terceros
    // SP: sp_listar_terceros
    public function terceros(): void
    {
        $this->render('cliente/terceros');
    }

    // POST /cliente/transferir
    // SP: sp_transferir
    public function transferir(): void
    {
        $this->render('cliente/transferir');
    }

    // GET /cliente/estado-cuenta
    // SP: sp_estado_cuenta
    public function estadoCuenta(): void
    {
        $this->render('cliente/estado-cuenta');
    }
}

