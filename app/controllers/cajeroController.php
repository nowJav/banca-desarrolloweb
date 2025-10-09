<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;
use App\core\Csrf;
use App\models\Cuenta;
use App\models\Movimiento;
use App\services\AuditoriaService;

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
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'cajero_crear_cuenta')) {
            $this->setFlash('error', 'CSRF inválido.');
            header('Location: /cajero/crear-cuenta');
            return;
        }

        $clienteId = (int)($_POST['cliente_id'] ?? 0);
        $tipo = trim((string)($_POST['tipo'] ?? ''));
        $saldoInicial = (float)($_POST['saldo_inicial'] ?? 0);
        if ($clienteId <= 0 || $tipo === '') {
            $this->setFlash('error', 'Datos incompletos.');
            header('Location: /cajero/crear-cuenta');
            return;
        }
        $res = (new Cuenta())->crearCuenta($clienteId, $tipo, $saldoInicial);
        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('crear_cuenta', 'Cuenta creada por cajero', 'cuenta', $res['id'] ?? null);
            $this->setFlash('success', $res['message'] ?? 'Cuenta creada.');
        } else {
            $this->setFlash('error', $res['message'] ?? 'No se pudo crear la cuenta.');
        }
        header('Location: /cajero/crear-cuenta');
    }

    // GET /cajero/deposito
    // SP: no aplica (muestra formulario)
    public function showDeposito(): void
    {
        $this->render('cajero/deposito');
    }

    // POST /cajero/deposito
    // SP: sp_deposito
    public function deposito(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'cajero_deposito')) {
            $this->setFlash('error', 'CSRF inválido.');
            header('Location: /cajero/deposito');
            return;
        }
        $cuenta = trim((string)($_POST['cuenta'] ?? ''));
        $monto = (float)($_POST['monto'] ?? 0);
        if ($cuenta === '' || $monto <= 0) {
            $this->setFlash('error', 'Datos inválidos.');
            header('Location: /cajero/deposito');
            return;
        }
        $res = (new Movimiento())->deposito($cuenta, $monto);
        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('deposito', 'Depósito en ventanilla', 'cuenta', null);
            $this->setFlash('success', $res['message'] ?? 'Depósito registrado.');
        } else {
            $this->setFlash('error', $res['message'] ?? 'No se pudo registrar el depósito.');
        }
        header('Location: /cajero/deposito');
    }

    // GET /cajero/retiro
    // SP: no aplica (muestra formulario)
    public function showRetiro(): void
    {
        $this->render('cajero/retiro');
    }

    // POST /cajero/retiro
    // SP: sp_retiro
    public function retiro(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'cajero_retiro')) {
            $this->setFlash('error', 'CSRF inválido.');
            header('Location: /cajero/retiro');
            return;
        }
        $cuenta = trim((string)($_POST['cuenta'] ?? ''));
        $monto = (float)($_POST['monto'] ?? 0);
        if ($cuenta === '' || $monto <= 0) {
            $this->setFlash('error', 'Datos inválidos.');
            header('Location: /cajero/retiro');
            return;
        }
        $res = (new Movimiento())->retiro($cuenta, $monto);
        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('retiro', 'Retiro en ventanilla', 'cuenta', null);
            $this->setFlash('success', $res['message'] ?? 'Retiro registrado.');
        } else {
            $this->setFlash('error', $res['message'] ?? 'No se pudo registrar el retiro.');
        }
        header('Location: /cajero/retiro');
    }
}
