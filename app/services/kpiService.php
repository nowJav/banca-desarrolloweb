<?php
declare(strict_types=1);

namespace App\services;

use App\config\Database;
use PDO;
use PDOException;

class KpiService
{
    public function kpisDia(): array
    {
        $db = Database::getConnection();
        try {
            $stmt = $db->query('CALL sp_kpis_dia()');
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            while ($stmt->nextRowset()) { /* flush */ }
            return $row;
        } catch (PDOException $e) {
            return [];
        }
    }
}
