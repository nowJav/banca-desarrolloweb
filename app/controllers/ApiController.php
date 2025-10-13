<?php
declare(strict_types=1);

namespace App\Controllers;

use App\core\Controller;
use App\config\Database;

class ApiController extends Controller
{
    public function kpiSeries(): void
    {
        header('Content-Type: application/json');
        $range = $_GET['range'] ?? 'today';
        $db = Database::getConnection();
        $start = new \DateTimeImmutable('today');
        $end = new \DateTimeImmutable('tomorrow');
        if ($range === 'yesterday') { $start = new \DateTimeImmutable('yesterday'); $end = new \DateTimeImmutable('today'); }
        if ($range === '7d') { $start = (new \DateTimeImmutable('today'))->modify('-6 days'); $end = new \DateTimeImmutable('tomorrow'); }
        $sql = "SELECT 
                  SUM(tipo='deposito') AS depositos,
                  SUM(tipo='retiro')   AS retiros
                FROM movimientos
                WHERE creado_at >= :ini AND creado_at < :fin";
        $stmt = $db->prepare($sql);
        $stmt->execute([':ini' => $start->format('Y-m-d 00:00:00'), ':fin' => $end->format('Y-m-d 00:00:00')]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: ['depositos'=>0,'retiros'=>0];
        echo json_encode(['depositos' => (int)$row['depositos'], 'retiros' => (int)$row['retiros']]);
    }

    public function terceroResumen(): void
    {
        header('Content-Type: application/json');
        $uid = (int)($_SESSION['user_id'] ?? 0);
        $tid = isset($_GET['tercero_id']) ? (int)$_GET['tercero_id'] : 0;
        if ($uid <= 0 || $tid <= 0) { echo json_encode(['conteo'=>0,'monto'=>0]); return; }
        $db = \App\config\Database::getConnection();
        // Verificar ownership y traer resumen
        $sql = 'SELECT tr.conteo, tr.monto_acumulado FROM terceros_resumen_diario tr JOIN terceros t ON t.id=tr.tercero_id WHERE t.usuario_owner_id=:uid AND tr.tercero_id=:tid AND tr.fecha=DATE(NOW()) LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->execute([':uid'=>$uid, ':tid'=>$tid]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: ['conteo'=>0,'monto_acumulado'=>0];
        echo json_encode(['conteo'=>(int)($row['conteo'] ?? 0), 'monto'=>(float)($row['monto_acumulado'] ?? 0)]);
    }
}
