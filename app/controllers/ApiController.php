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
}

