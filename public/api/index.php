<?php
declare(strict_types=1);
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/lib/loadEnv.php';
require_once __DIR__ . '/../../src/lib/ipInRange.php';
use Zerlix\KvmDash\Api\Controller\Controller;

// Load .env file
try {
    loadEnv(__DIR__ . '/../../.env');
} catch (Exception $e) {
    echo 'Fehler: ' . $e->getMessage();
    exit();
}

// Debug mode
if (getenv('DEBUG') === 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} 

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


// ip check
$allowedIps = explode(',', getenv('ALLOWED_IPS'));
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
$ipAllowed = false;
foreach ($allowedIps as $allowedIp) {
    if (ipInRange($clientIp, $allowedIp)) {
        $ipAllowed = true;
        break;
    }
}

// If IP is not allowed, return 403
if (!$ipAllowed) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit();
}


// JSON header
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