<?php
require_once __DIR__ . '/../vendor/autoload.php';

// json header
header('Content-Type: application/json');

// Route bereinigen
$route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Controller instanzieren
try {
    $controller = new \Zerlix\Backend\Controller\Controller();
    echo json_encode($controller->handle($route, $method));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}