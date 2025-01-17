<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../backend/lib/loadEnv.php';
require_once __DIR__ . '/../backend/lib/ipInRange.php';
use Zerlix\KvmDash\Api\Controller\Controller;

// Error reporting
if (getenv('DEBUG') === 'true') {
    // Enable error reporting and display errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} 

// json header
header('Content-Type: application/json');

// Load .env file
loadEnv(__DIR__ . '/../.env');


// Sicherheitsüberprüfung basierend auf IP
$allowedIps = explode(',', getenv('ALLOWED_IPS'));
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
$ipAllowed = false;
foreach ($allowedIps as $allowedIp) {
    if (ipInRange($clientIp, $allowedIp)) {
        $ipAllowed = true;
        break;
    }
}
if (!$ipAllowed) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}


// json header
header('Content-Type: application/json');


// Route bereinigen
$route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];



// Controller instanzieren
try {
    $controller = new Controller();
    echo json_encode($controller->handle($route, $method));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}