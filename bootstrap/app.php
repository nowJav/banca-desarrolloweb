<?php
declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$rootPath = dirname(__DIR__);
$dotenv = Dotenv::createImmutable($rootPath);
$dotenv->safeLoad();

// Initialize base app settings (timezone, locale)
\App\Config\App::init();

// Optionally start session based on env
$sessionName = $_ENV['SESSION_NAME'] ?? $_SERVER['SESSION_NAME'] ?? null;
if ($sessionName) {
    if (session_status() === PHP_SESSION_NONE) {
        session_name($sessionName);
        if (isset($_ENV['SESSION_LIFETIME'])) {
            ini_set('session.gc_maxlifetime', (string) $_ENV['SESSION_LIFETIME']);
        }
        session_start();
    }
}

return [
    'routes' => \App\Config\Routes::all(),
];

