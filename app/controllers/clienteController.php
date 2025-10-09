<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;
use App\core\Csrf;
use App\models\Tercero;
use App\models\Transferencia;
use App\models\Movimiento;
use App\services\AuditoriaService;

class ClienteController extends Controller
{
    // GET /cliente/terceros
    // SP: sp_listar_terceros
    public function terceros(): void
    {
        $clienteId = $_SESSION['user_id'] ?? null;
        $terceros = [];
        if ($clienteId) {
            $terceros = (new Tercero())->listarPorCliente((int)$clienteId);
        }
        $this->render('cliente/terceros', ['terceros' => $terceros]);
    }

    // GET /cliente/transferir
    public function showTransferir(): void
    {
        $clienteId = $_SESSION['user_id'] ?? null;
        $terceros = [];
        if ($clienteId) {
            $terceros = (new Tercero())->listarPorCliente((int)$clienteId);
        }
        $this->render('cliente/transferir', ['terceros' => $terceros]);
    }

    // POST /cliente/terceros
    // SP: sp_agregar_tercero
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
        if ($clienteId <= 0 || $nombre === '' || $documento === '' || $banco === '' || $cuenta === '') {
            $this->setFlash('error', 'Completa todos los campos.');
            header('Location: /cliente/terceros');
            return;
        }
        $res = (new Tercero())->agregar($clienteId, $nombre, $documento, $banco, $cuenta);
        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('agregar_tercero', 'Alta de tercero', 'tercero', null);
            $this->setFlash('success', $res['message'] ?? 'Tercero agregado.');
        } else {
            $this->setFlash('error', $res['message'] ?? 'No se pudo agregar.');
        }
        header('Location: /cliente/terceros');
    }

    // POST /cliente/transferir
    // SP: sp_transferir
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
        if ($clienteId <= 0 || $terceroId <= 0 || $monto <= 0) {
            $this->setFlash('error', 'Datos inválidos para transferir.');
            header('Location: /cliente/transferir');
            return;
        }
        $res = (new Transferencia())->transferir($clienteId, $terceroId, $monto, $descripcion ?: null);
        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('transferir', 'Transferencia a tercero', 'tercero', $terceroId);
            $this->setFlash('success', $res['message'] ?? 'Transferencia realizada.');
        } else {
            $msg = $res['message'] ?? 'No se pudo transferir.';
            $this->setFlash('error', $msg);
        }
        header('Location: /cliente/transferir');
    }

    // GET /cliente/estado-cuenta
    // SP: sp_listado_estado_cuenta
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
