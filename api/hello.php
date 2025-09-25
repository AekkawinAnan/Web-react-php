<?php
// Bank Slip Reader - Simple Hello World API
// This is a simple PHP file that returns hello world

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple hello world response
$response = [
    'status' => 'success',
    'message' => 'Hello World from Bank Slip Reader!',
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '1.0.0',
    'data' => [
        'service' => 'Bank Slip Reader Backend',
        'description' => 'Simple PHP API for testing',
        'method' => $_SERVER['REQUEST_METHOD'],
        'path' => $_SERVER['REQUEST_URI'],
        'server_info' => [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
        ]
    ]
];

// Return JSON response
http_response_code(200);
echo json_encode($response, JSON_PRETTY_PRINT);
?>
