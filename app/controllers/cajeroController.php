<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;

class CajeroController extends Controller
{
    // GET /cajero/crear-cuenta
    // SP: no aplica (muestra formulario)
    public function showCrearCuenta(): void
    {
        $this->render('cajero/crear-cuenta');
    }

    // POST /cajero/crear-cuenta
    // SP: sp_crear_cuenta
    public function crearCuenta(): void
    {
        $this->render('cajero/crear-cuenta');
    }

    // GET /cajero/deposito
    // SP: no aplica (muestra formulario)
    public function showDeposito(): void
    {
        $this->render('cajero/deposito');
    }

    // POST /cajero/deposito
    // SP: sp_registrar_deposito
    public function deposito(): void
    {
        $this->render('cajero/deposito');
    }

    // GET /cajero/retiro
    // SP: no aplica (muestra formulario)
    public function showRetiro(): void
    {
        $this->render('cajero/retiro');
    }

    // POST /cajero/retiro
    // SP: sp_registrar_retiro
    public function retiro(): void
    {
        $this->render('cajero/retiro');
    }
}

