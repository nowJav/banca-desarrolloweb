<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;
use App\core\Csrf;
use App\models\Cuenta;
use App\models\Movimiento;
use App\services\AuditoriaService;
use App\helpers\Validator;

class CajeroController extends Controller
{
    public function index(): void
    {
        $uid = (int)($_SESSION['user_id'] ?? 0);
        $db = \App\config\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM movimientos WHERE DATE(creado_at)=DATE(NOW()) AND creado_por=:uid");
        $stmt->execute([':uid' => $uid]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: ['cnt' => 0];
        $this->render('cajero/index', ['hoy' => (int)$row['cnt']]);
    }
    public function showCrearCuenta(): void
    {
        $this->render('cajero/crear-cuenta');
    }

    public function crearCuenta(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'cajero_crear_cuenta')) {
            $this->setFlash('error', 'CSRF inválido.');
            header('Location: /cajero/crear-cuenta');
            return;
        }

        $nombre = trim((string)($_POST['nombre'] ?? ''));
        $dpi = trim((string)($_POST['dpi'] ?? ''));
        $numero = trim((string)($_POST['numero_cuenta'] ?? ''));
        $saldoInicial = (float)($_POST['saldo_inicial'] ?? 0);
        if (!Validator::stringLen($nombre, 2, 140) || !Validator::digits($dpi, 6, 25) || !Validator::stringLen($numero, 6, 24) || $saldoInicial < 0) {
            $this->setFlash('error', 'Datos inválidos.');
            header('Location: /cajero/crear-cuenta');
            return;
        }
        // Validar número de cuenta único
        $db = \App\config\Database::getConnection();
        $stmt = $db->prepare('SELECT 1 FROM cuentas WHERE numero_cuenta = :num LIMIT 1');
        $stmt->execute([':num' => $numero]);
        if ($stmt->fetch()) {
            $this->setFlash('error', 'Número de cuenta ya existe.');
            header('Location: /cajero/crear-cuenta');
            return;
        }
        $uid = (int)($_SESSION['user_id'] ?? 0);
        $res = (new Cuenta())->crearCuentaCajero($numero, $dpi, $nombre, $saldoInicial, $uid);
        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('crear_cuenta', 'cuenta', $numero);
            $this->setFlash('success', 'Cuenta creada: ' . $numero);
            $this->render('cajero/crear-cuenta', ['numero' => $numero]);
            return;
        }
        $this->setFlash('error', 'No se pudo crear la cuenta.');
        header('Location: /cajero/crear-cuenta');
    }

    public function showDeposito(): void
    {
        $this->render('cajero/deposito');
    }

    public function deposito(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'cajero_deposito')) {
            $this->setFlash('error', 'CSRF inválido.');
            header('Location: /cajero/deposito');
            return;
        }
        $numero = trim((string)($_POST['numero_cuenta'] ?? ''));
        $monto = (float)($_POST['monto'] ?? 0);
        $glosa = trim((string)($_POST['glosa'] ?? '')) ?: null;
        if ($numero === '' || !Validator::positiveNumber($monto) || !Validator::maxNumber($monto, 1000000000)) {
            $this->setFlash('error', 'Datos inválidos.');
            header('Location: /cajero/deposito');
            return;
        }
        $cuentaModel = new \App\models\Cuenta();
        $saldoAntes = $cuentaModel->saldoPorNumero($numero);
        if ($saldoAntes === null) { $this->setFlash('error','Cuenta no existe o no activa.'); header('Location:/cajero/deposito'); return; }
        $uid = (int)($_SESSION['user_id'] ?? 0);
        $res = (new Movimiento())->depositoCajero($numero, $monto, $uid, $glosa);
        if ($res['ok'] ?? false) {
            $saldoDespues = $cuentaModel->saldoPorNumero($numero);
            (new AuditoriaService())->registrar('deposito', 'cuenta', $numero);
            $this->setFlash('success', 'Depósito registrado.');
            $this->render('cajero/deposito', ['numero' => $numero, 'saldo_antes' => $saldoAntes, 'saldo_despues' => $saldoDespues]);
            return;
        }
        $this->setFlash('error', 'No se pudo registrar el depósito.');
        header('Location: /cajero/deposito');
    }

    public function showRetiro(): void
    {
        $this->render('cajero/retiro');
    }

    public function retiro(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'cajero_retiro')) {
            $this->setFlash('error', 'CSRF inválido.');
            header('Location: /cajero/retiro');
            return;
        }
        $numero = trim((string)($_POST['numero_cuenta'] ?? ''));
        $monto = (float)($_POST['monto'] ?? 0);
        $glosa = trim((string)($_POST['glosa'] ?? '')) ?: null;
        if ($numero === '' || !Validator::positiveNumber($monto) || !Validator::maxNumber($monto, 1000000000)) {
            $this->setFlash('error', 'Datos inválidos.');
            header('Location: /cajero/retiro');
            return;
        }
        $cuentaModel = new \App\models\Cuenta();
        $saldoAntes = $cuentaModel->saldoPorNumero($numero);
        if ($saldoAntes === null) { $this->setFlash('warning','Cuenta no activa.'); header('Location:/cajero/retiro'); return; }
        if ($saldoAntes < $monto) { $this->setFlash('danger','Saldo insuficiente.'); header('Location:/cajero/retiro'); return; }
        $uid = (int)($_SESSION['user_id'] ?? 0);
        $res = (new Movimiento())->retiroCajero($numero, $monto, $uid, $glosa);
        if ($res['ok'] ?? false) {
            $saldoDespues = $cuentaModel->saldoPorNumero($numero);
            (new AuditoriaService())->registrar('retiro', 'cuenta', $numero);
            $this->setFlash('success', 'Retiro registrado.');
            $this->render('cajero/retiro', ['numero' => $numero, 'saldo_antes' => $saldoAntes, 'saldo_despues' => $saldoDespues]);
            return;
        }
        $this->setFlash('error', 'No se pudo registrar el retiro.');
        header('Location: /cajero/retiro');
    }

    public function showMovimientos(): void
    {
        $numero = isset($_GET['numero']) ? trim((string)$_GET['numero']) : '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(100, (int)($_GET['perPage'] ?? 20)));
        $rows = [];
        $total = 0;
        if ($numero !== '') {
            $db = \App\config\Database::getConnection();
            // Total
            $stmt = $db->prepare('SELECT COUNT(*) AS cnt FROM movimientos m JOIN cuentas c ON c.id=m.cuenta_id WHERE c.numero_cuenta = :num');
            $stmt->execute([':num' => $numero]);
            $total = (int)($stmt->fetchColumn() ?: 0);
            // Page
            $offset = ($page - 1) * $perPage;
            $sql = 'SELECT m.creado_at, m.tipo, m.monto, m.glosa, u.email AS operador
                    FROM movimientos m
                    JOIN cuentas c ON c.id=m.cuenta_id
                    LEFT JOIN usuarios u ON u.id=m.creado_por
                    WHERE c.numero_cuenta = :num
                    ORDER BY m.creado_at DESC
                    LIMIT :lim OFFSET :off';
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':num', $numero);
            $stmt->bindValue(':lim', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }
        $this->render('cajero/movimientos', [
            'numero' => $numero,
            'rows' => $rows,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
        ]);
    }
}
