<?php
require_once __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Debug
error_log("Request URI: " . $_SERVER['REQUEST_URI']);

// Route bereinigen
$route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Debug
error_log("Parsed Route: " . $route);
error_log("Method: " . $method);

try {
    $controller = new \Zerlix\Backend\Controller\SystemController();
    echo json_encode($controller->handle($route, $method));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}