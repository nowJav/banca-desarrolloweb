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
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $db = \App\config\Database::getConnection();
        $sql = 'SELECT u.id as usuario_id, c.id as cajero_id, c.nombre, u.email, c.activo, u.ultimo_login_at
                FROM cajeros c JOIN usuarios u ON u.id=c.usuario_id';
        $params = [];
        if ($q !== '') {
            $sql .= ' WHERE c.nombre LIKE :q OR u.email LIKE :q';
            $params[':q'] = '%' . $q . '%';
        }
        $sql .= ' ORDER BY c.nombre ASC LIMIT 100';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $cajeros = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $this->render('admin/cajeros', ['cajeros' => $cajeros, 'q' => $q]);
    }

    public function cuentas(): void
    {
        $numero = isset($_GET['numero']) ? trim((string)$_GET['numero']) : '';
        $dpi    = isset($_GET['dpi']) ? trim((string)$_GET['dpi']) : '';
        $cuenta = null; $movs = [];
        if ($numero !== '' || $dpi !== '') {
            $m = new \App\models\Cuenta();
            $cuenta = $m->buscarPorNumeroODpi($numero ?: null, $dpi ?: null);
            if ($cuenta) {
                $movs = $m->ultimosMovimientos((int)$cuenta['id']);
            }
        }
        $this->render('admin/cuentas', ['cuenta' => $cuenta, 'movs' => $movs, 'numero' => $numero, 'dpi' => $dpi]);
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

    // GET /admin/auditoria
    public function auditoria(): void
    {
        $db = \App\config\Database::getConnection();
        $desde = isset($_GET['desde']) && $_GET['desde'] !== '' ? (string)$_GET['desde'] : null;
        $hasta = isset($_GET['hasta']) && $_GET['hasta'] !== '' ? (string)$_GET['hasta'] : null;
        $entidad = isset($_GET['entidad']) && $_GET['entidad'] !== '' ? (string)$_GET['entidad'] : null;
        $usuarioId = isset($_GET['usuario_id']) && $_GET['usuario_id'] !== '' ? (int)$_GET['usuario_id'] : null;
        $ip = isset($_GET['ip']) && $_GET['ip'] !== '' ? (string)$_GET['ip'] : null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(100, (int)($_GET['perPage'] ?? 50)));

        // Usuarios para select
        $users = $db->query('SELECT id, email FROM usuarios ORDER BY email ASC')->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Build query
        $where = [];$params=[];
        if ($desde) { $where[] = 'a.creado_at >= :desde'; $params[':desde'] = $desde.' 00:00:00'; }
        if ($hasta) { $where[] = 'a.creado_at <= :hasta'; $params[':hasta'] = $hasta.' 23:59:59'; }
        if ($entidad) { $where[] = 'a.entidad = :entidad'; $params[':entidad'] = $entidad; }
        if ($usuarioId) { $where[] = 'a.usuario_id = :uid'; $params[':uid'] = $usuarioId; }
        if ($ip) { $where[] = 'a.ip = :ip'; $params[':ip'] = $ip; }
        $wsql = empty($where) ? '' : (' WHERE ' . implode(' AND ', $where));

        // Count
        $stmt = $db->prepare('SELECT COUNT(*) FROM auditoria_eventos a'.$wsql);
        $stmt->execute($params);
        $total = (int)($stmt->fetchColumn() ?: 0);
        // Page
        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT a.creado_at, u.email as usuario, a.entidad, a.entidad_id, a.accion, a.ip, a.datos_previos, a.datos_nuevos
                FROM auditoria_eventos a LEFT JOIN usuarios u ON u.id=a.usuario_id'
                . $wsql . ' ORDER BY a.creado_at DESC LIMIT :lim OFFSET :off';
        $stmt = $db->prepare($sql);
        foreach ($params as $k=>$v) { $stmt->bindValue($k, $v); }
        $stmt->bindValue(':lim', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->render('admin/auditoria', [
            'rows' => $rows,
            'users' => $users,
            'desde' => $desde,
            'hasta' => $hasta,
            'entidad' => $entidad,
            'usuario_id' => $usuarioId,
            'ip' => $ip,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
        ]);
    }

    // POST /admin/cajeros (crear)
    public function crearCajero(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!\App\core\Csrf::validate($token, 'admin_cajeros')) {
            $this->setFlash('error', 'CSRF inválido.'); header('Location: /admin/cajeros'); return;
        }
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        $email  = trim((string)($_POST['email'] ?? ''));
        $pass   = (string)($_POST['password'] ?? '');
        $pass2  = (string)($_POST['password_confirmation'] ?? '');
        if ($nombre === '' || $email === '' || $pass === '' || $pass !== $pass2) {
            $this->setFlash('error', 'Datos inválidos para crear cajero.'); header('Location: /admin/cajeros'); return;
        }
        $db = \App\config\Database::getConnection();
        try {
            $db->beginTransaction();
            $stmt = $db->prepare('INSERT INTO usuarios (email, pass_hash, role_id, activo) VALUES (:email,:hash,2,1)');
            $stmt->execute([':email' => $email, ':hash' => \App\core\Auth::hashPassword($pass)]);
            $uid = (int)$db->lastInsertId();
            $stmt = $db->prepare('INSERT INTO cajeros (usuario_id, nombre, activo) VALUES (:uid,:nombre,1)');
            $stmt->execute([':uid' => $uid, ':nombre' => $nombre]);
            $db->commit();
            (new \App\services\AuditoriaService())->registrar('crear_cajero', 'usuario', $uid);
            $this->setFlash('success', 'Cajero creado.');
        } catch (\PDOException $e) {
            $db->rollBack();
            $this->setFlash('error', 'No se pudo crear el cajero.');
        }
        header('Location: /admin/cajeros');
    }

    // POST /admin/cajeros/toggle
    public function toggleCajero(): void
    {
        $token = (string)($_POST['csrf_token'] ?? '');
        if (!\App\core\Csrf::validate($token, 'admin_cajeros')) {
            $this->setFlash('error', 'CSRF inválido.'); header('Location: /admin/cajeros'); return;
        }
        $uid = (int)($_POST['usuario_id'] ?? 0);
        $activo = (int)($_POST['activo'] ?? 0) ? 1 : 0;
        $db = \App\config\Database::getConnection();
        try {
            $db->beginTransaction();
            $stmt = $db->prepare('UPDATE usuarios SET activo=:act WHERE id=:uid');
            $stmt->execute([':act' => $activo, ':uid' => $uid]);
            $stmt = $db->prepare('UPDATE cajeros SET activo=:act WHERE usuario_id=:uid');
            $stmt->execute([':act' => $activo, ':uid' => $uid]);
            $db->commit();
            (new \App\services\AuditoriaService())->registrar($activo ? 'activar_cajero' : 'desactivar_cajero', 'usuario', $uid);
            $this->setFlash('success', 'Estado actualizado.');
        } catch (\PDOException $e) {
            $db->rollBack();
            $this->setFlash('error', 'No se pudo actualizar el estado.');
        }
        header('Location: /admin/cajeros');
    }
}
