<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;
use App\core\Csrf;
use App\models\Tercero;
use App\models\Transferencia;
use App\models\Movimiento;
use App\services\AuditoriaService;
use App\helpers\Validator;

class ClienteController extends Controller
{
    public function terceros(): void
    {
        $clienteId = $_SESSION['user_id'] ?? null;
        $terceros = [];
        if ($clienteId) {
            $terceros = (new Tercero())->listarPorCliente((int)$clienteId);
        }
        $this->render('cliente/terceros', ['terceros' => $terceros]);
    }

    public function showTransferir(): void
    {
        $clienteId = $_SESSION['user_id'] ?? null;
        $terceros = [];
        if ($clienteId) {
            $terceros = (new Tercero())->listarPorCliente((int)$clienteId);
        }
        $this->render('cliente/transferir', ['terceros' => $terceros]);
    }

    public function agregarTercero(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'cliente_terceros')) {
            $this->setFlash('error', 'CSRF inválido.');
            header('Location: /cliente/terceros');
            return;
        }
        $clienteId = (int)($_SESSION['user_id'] ?? 0);
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        $documento = trim((string)($_POST['documento'] ?? ''));
        $banco = trim((string)($_POST['banco'] ?? ''));
        $cuenta = trim((string)($_POST['cuenta'] ?? ''));
        if ($clienteId <= 0 || !Validator::stringLen($nombre, 2, 80) || !Validator::stringLen($banco, 2, 60) || !Validator::stringLen($cuenta, 4, 30) || !Validator::digits($documento, 6, 25)) {
            $this->setFlash('error', 'Completa todos los campos.');
            header('Location: /cliente/terceros');
            return;
        }
        $res = (new Tercero())->agregar($clienteId, $nombre, $documento, $banco, $cuenta);
        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('agregar_tercero', 'tercero', null);
            $this->setFlash('success', $res['message'] ?? 'Tercero agregado.');
        } else {
            $this->setFlash('error', 'No se pudo agregar.');
        }
        header('Location: /cliente/terceros');
    }

    public function transferir(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'cliente_transferir')) {
            $this->setFlash('error', 'CSRF inválido.');
            header('Location: /cliente/terceros');
            return;
        }
        $clienteId = (int)($_SESSION['user_id'] ?? 0);
        $terceroId = (int)($_POST['tercero_id'] ?? 0);
        $monto = (float)($_POST['monto'] ?? 0);
        $descripcion = trim((string)($_POST['descripcion'] ?? ''));
        if ($clienteId <= 0 || $terceroId <= 0 || !Validator::positiveNumber($monto) || !Validator::maxNumber($monto, 1000000000)) {
            $this->setFlash('error', 'Datos inválidos para transferir.');
            header('Location: /cliente/transferir');
            return;
        }
        $res = (new Transferencia())->transferir($clienteId, $terceroId, $monto, $descripcion ?: null);
        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('transferir', 'tercero', $terceroId);
            $this->setFlash('success', $res['message'] ?? 'Transferencia realizada.');
        } else {
            $this->setFlash('error', 'No se pudo transferir.');
        }
        header('Location: /cliente/transferir');
    }

    public function estadoCuenta(): void
    {
        $clienteId = (int)($_SESSION['user_id'] ?? 0);
        $desde = isset($_GET['desde']) && $_GET['desde'] !== '' ? (string)$_GET['desde'] : null;
        $hasta = isset($_GET['hasta']) && $_GET['hasta'] !== '' ? (string)$_GET['hasta'] : null;
        $movs = [];
        if ($clienteId > 0) {
            $movs = (new Movimiento())->estadoCuenta($clienteId, $desde, $hasta);
        }
        $this->render('cliente/estado-cuenta', ['movimientos' => $movs, 'desde' => $desde, 'hasta' => $hasta]);
    }
}

