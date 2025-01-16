<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/lib/loadEnv.php';
require_once __DIR__ . '/../src/lib/ipInRange.php';

use Zerlix\KvmDash\Api\Controller\Controller;

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

/*--------------- DISABLED FOR TESTING
// Sicherheitsüberprüfung basierend auf API-Token
$token = $_SERVER['HTTP_X_API_TOKEN'] ?? '';
if ($token !== getenv('API_TOKEN')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    
    /// exit;
}
------------------------------- */




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