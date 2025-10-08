<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;

class AdminController extends Controller
{
    // GET /admin
    // SP: sp_kpis_admin (opcional para dashboard)
    public function index(): void
    {
        $this->render('admin/index');
    }

    // GET /admin/cajeros
    // SP: sp_listar_cajeros
    public function cajeros(): void
    {
        $this->render('admin/cajeros');
    }

    // GET /admin/cuentas
    // SP: sp_listar_cuentas
    public function cuentas(): void
    {
        $this->render('admin/cuentas');
    }
}

