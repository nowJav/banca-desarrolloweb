<?php
declare(strict_types=1);

namespace App\config;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = (string) App::env('DB_HOST', '127.0.0.1');
            $port = (int) (App::env('DB_PORT', 3306));
            $dbname = (string) App::env('DB_NAME', '');
            $charset = (string) App::env('DB_CHARSET', 'utf8mb4');
            $user = (string) App::env('DB_USER', 'root');
            $pass = (string) App::env('DB_PASS', '');

            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $dbname, $charset);

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            self::$instance = new PDO($dsn, $user, $pass, $options);
        }

        return self::$instance;
    }
}

