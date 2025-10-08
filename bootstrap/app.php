<?php
declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$rootPath = dirname(__DIR__);
$dotenv = Dotenv::createImmutable($rootPath);
$dotenv->safeLoad();

// Initialize base app settings (timezone, locale)
\App\config\App::init();

// Setup DB-backed session handler and start session
$sessionName = $_ENV['SESSION_NAME'] ?? $_SERVER['SESSION_NAME'] ?? null;
if ($sessionName && session_status() === PHP_SESSION_NONE) {
    session_name($sessionName);
}
if (isset($_ENV['SESSION_LIFETIME'])) {
    ini_set('session.gc_maxlifetime', (string) $_ENV['SESSION_LIFETIME']);
}

$handler = new \App\core\DbSessionHandler();
session_set_save_handler($handler, true);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

return [
    'routes' => \App\config\Routes::all(),
];

