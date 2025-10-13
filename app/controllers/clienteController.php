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
    public function index(): void
    {
        $uid = (int)($_SESSION['user_id'] ?? 0);
        $db = \App\config\Database::getConnection();
        $sqlSaldo = 'SELECT IFNULL(SUM(c.saldo),0) FROM cuentas c JOIN clientes cl ON cl.id=c.cliente_id WHERE cl.usuario_id = :uid';
        $stmt = $db->prepare($sqlSaldo); $stmt->execute([':uid'=>$uid]); $saldoTotal = (float)($stmt->fetchColumn() ?: 0);
        $sqlTx = 'SELECT COUNT(*), IFNULL(SUM(monto),0) FROM transferencias WHERE creado_por = :uid AND DATE(creado_at)=DATE(NOW())';
        $stmt = $db->prepare($sqlTx); $stmt->execute([':uid'=>$uid]); $row = $stmt->fetch(\PDO::FETCH_NUM) ?: [0,0];
        $this->render('cliente/index', ['saldoTotal'=>$saldoTotal, 'txHoy'=>(int)$row[0], 'montoHoy'=>(float)$row[1]]);
    }
    public function terceros(): void
    {
        $clienteId = $_SESSION['user_id'] ?? null;
        $terceros = [];
        if ($clienteId) {
            $terceros = (new Tercero())->listarPorUsuario((int)$clienteId);
        }
        $this->render('cliente/terceros', ['terceros' => $terceros]);
    }

    public function showTransferir(): void
    {
        $clienteId = $_SESSION['user_id'] ?? null;
        $terceros = [];
        if ($clienteId) {
            $terceros = (new Tercero())->listarPorUsuario((int)$clienteId);
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
        $numero = trim((string)($_POST['numero_cuenta'] ?? ''));
        $alias = trim((string)($_POST['alias'] ?? ''));
        $montoMax = (float)($_POST['monto_max_op'] ?? 0);
        $maxDiarias = (int)($_POST['max_tx_diarias'] ?? 0);
        if ($clienteId <= 0 || !Validator::stringLen($alias, 2, 80) || !Validator::stringLen($numero, 6, 24) || !Validator::positiveNumber($montoMax) || $maxDiarias <= 0) {
            $this->setFlash('error', 'Completa todos los campos correctamente.');
            header('Location: /cliente/terceros');
            return;
        }
        $res = (new Tercero())->agregar($clienteId, $numero, $alias, $montoMax, $maxDiarias);
        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('agregar_tercero', 'tercero', null);
            $this->setFlash('success', $res['message'] ?? 'Tercero agregado.');
        } else {
            $this->setFlash('error', 'No se pudo agregar.');
        }
        header('Location: /cliente/terceros');
    }

    // POST /cliente/terceros/toggle
    public function toggleTercero(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!Csrf::validate($token, 'cliente_terceros')) {
            $this->setFlash('error', 'CSRF inválido.');
            header('Location: /cliente/terceros');
            return;
        }
        $uid = (int)($_SESSION['user_id'] ?? 0);
        $tid = (int)($_POST['tercero_id'] ?? 0);
        $activo = ((int)($_POST['activo'] ?? 0)) === 1;
        if ($uid <= 0 || $tid <= 0) { $this->setFlash('error','Datos inválidos.'); header('Location:/cliente/terceros'); return; }
        $ok = (new Tercero())->activar($tid, $uid, $activo);
        if ($ok) {
            $this->setFlash('success', $activo ? 'Tercero activado.' : 'Tercero desactivado.');
        } else {
            $this->setFlash('error', 'No se pudo actualizar.');
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
        if ($clienteId <= 0 || $terceroId <= 0 || !Validator::positiveNumber($monto) || !Validator::maxNumber($monto, 1000000000)) {
            $this->setFlash('error', 'Datos inválidos para transferir.');
            header('Location: /cliente/transferir');
            return;
        }
        // Resolver cuenta origen (primera activa del cliente)
        $db = \App\config\Database::getConnection();
        $stmt = $db->prepare('SELECT c.numero_cuenta FROM cuentas c JOIN clientes cl ON cl.id=c.cliente_id WHERE cl.usuario_id=:uid AND c.estado="activa" ORDER BY c.id ASC LIMIT 1');
        $stmt->execute([':uid' => $clienteId]);
        $origen = ($stmt->fetchColumn()) ?: null;
        if (!$origen) { $this->setFlash('error','No tienes cuenta activa.'); header('Location:/cliente/transferir'); return; }
        // Resolver cuenta destino desde tercero
        $stmt = $db->prepare('SELECT c.numero_cuenta FROM terceros t JOIN cuentas c ON c.id=t.cuenta_tercero_id WHERE t.id=:tid AND t.usuario_owner_id=:uid AND t.activo=1 LIMIT 1');
        $stmt->execute([':tid'=>$terceroId, ':uid'=>$clienteId]);
        $destino = ($stmt->fetchColumn()) ?: null;
        if (!$destino) { $this->setFlash('error','Tercero no registrado o inactivo.'); header('Location:/cliente/transferir'); return; }
        $res = (new Transferencia())->transferir($clienteId, (string)$origen, (string)$destino, $monto);
        if ($res['ok'] ?? false) {
            (new AuditoriaService())->registrar('transferir', 'tercero', $terceroId);
            $tx = $res['id_tx'] ?? null;
            $this->setFlash('success', 'Transferencia realizada' . ($tx ? (' · ID ' . $tx) : ''));
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
        $format = isset($_GET['format']) ? (string)$_GET['format'] : '';
        $db = \App\config\Database::getConnection();
        $cuentas = $db->prepare('SELECT c.numero_cuenta FROM cuentas c JOIN clientes cl ON cl.id=c.cliente_id WHERE cl.usuario_id=:uid ORDER BY c.id ASC');
        $cuentas->execute([':uid'=>$clienteId]);
        $lista = array_map(fn($r)=>$r['numero_cuenta'], $cuentas->fetchAll(\PDO::FETCH_ASSOC) ?: []);
        $numero = isset($_GET['numero']) && $_GET['numero']!=='' ? (string)$_GET['numero'] : ($lista[0] ?? null);
        $movs = [];
        if ($numero) {
            $movs = (new Movimiento())->estadoCuentaPorNumero((string)$numero, $desde, $hasta);
        }
        if ($format === 'csv' && $numero) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="estado_cuenta_' . preg_replace('/[^A-Za-z0-9_-]/','',$numero) . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Fecha/Hora','Tipo','Crédito','Débito','id_tx','Glosa']);
            foreach ($movs as $m) {
                fputcsv($out, [
                    (string)($m['creado_at'] ?? ''),
                    (string)($m['tipo'] ?? ''),
                    (string)($m['credito'] ?? ''),
                    (string)($m['debito'] ?? ''),
                    (string)($m['id_tx'] ?? ''),
                    (string)($m['glosa'] ?? ''),
                ]);
            }
            fclose($out);
            return;
        }
        $this->render('cliente/estado-cuenta', ['movimientos' => $movs, 'desde' => $desde, 'hasta' => $hasta, 'numero' => $numero, 'cuentas' => $lista]);
    }
}
