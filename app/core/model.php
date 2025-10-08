<?php
declare(strict_types=1);

namespace App\core;

use App\config\Database;
use PDO;

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }
}

