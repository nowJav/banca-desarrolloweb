<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;
use App\services\KpiService;
use App\core\Csrf;
use App\models\Cuenta;
use App\services\AuditoriaService;

class AdminController extends Controller
{
    public function index(): void
    {
        $kpis = (new KpiService())->kpisDia();
        $this->render('admin/index', ['kpis' => $kpis]);
    }

    public function cajeros(): void
    {
        $this->render('admin/cajeros');
    }

    public function cuentas(): void
    {
        $this->render('admin/cuentas');
    }

    public function bloquearCuenta(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'admin_cuentas')) {
            $this->setFlash('error', 'CSRF inválido.');
            header('Location: /admin/cuentas');
            return;
        }
        $cuentaId = (int)($_POST['cuenta_id'] ?? 0);
        if ($cuentaId <= 0) { $this->setFlash('error', 'Cuenta inválida.'); header('Location: /admin/cuentas'); return; }
        $res = (new Cuenta())->bloquearCuenta($cuentaId);
        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('bloquear_cuenta', 'cuenta', $cuentaId);
            $this->setFlash('success', $res['message'] ?? 'Cuenta bloqueada.');
        } else {
            $this->setFlash('error', 'No se pudo bloquear.');
        }
        header('Location: /admin/cuentas');
    }

    public function desbloquearCuenta(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'admin_cuentas')) {
            $this->setFlash('error', 'CSRF inválido.');
            header('Location: /admin/cuentas');
            return;
        }
        $cuentaId = (int)($_POST['cuenta_id'] ?? 0);
        if ($cuentaId <= 0) { $this->setFlash('error', 'Cuenta inválida.'); header('Location: /admin/cuentas'); return; }
        $res = (new Cuenta())->desbloquearCuenta($cuentaId);
        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('desbloquear_cuenta', 'cuenta', $cuentaId);
            $this->setFlash('success', $res['message'] ?? 'Cuenta desbloqueada.');
        } else {
            $this->setFlash('error', 'No se pudo desbloquear.');
        }
        header('Location: /admin/cuentas');
    }
}

