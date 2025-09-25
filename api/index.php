<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple routing based on request method and path
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Remove query string from URI
$uri_parts = explode('?', $request_uri);
$path = $uri_parts[0];

// Remove leading slash and split path
$path_parts = explode('/', trim($path, '/'));
$endpoint = $path_parts[0] ?? '';

try {
    switch ($endpoint) {
        case 'test':
            // GET /api/test - Hello World endpoint
            if ($request_method === 'GET') {
                $response = [
                    'status' => 'success',
                    'message' => 'Hello World from Bank Slip Reader API!',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'version' => '1.0.0',
                    'data' => [
                        'service' => 'Bank Slip Reader Backend API',
                        'description' => 'OCR-based bank slip reading service',
                        'endpoints' => [
                            'GET /api/test' => 'Test endpoint - returns hello world',
                            'POST /api/verify-slip' => 'Verify slip data (planned)',
                            'POST /api/parse-slip' => 'Parse slip image (planned)',
                            'POST /api/ocr' => 'Process image with OCR and extract slip data'
                        ]
                    ]
                ];
                http_response_code(200);
                echo json_encode($response, JSON_PRETTY_PRINT);
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'health':
            // GET /api/health - Health check endpoint
            if ($request_method === 'GET') {
                $response = [
                    'status' => 'healthy',
                    'message' => 'API is running correctly',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'server_info' => [
                        'php_version' => PHP_VERSION,
                        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                        'request_method' => $request_method,
                        'request_uri' => $request_uri
                    ]
                ];
                http_response_code(200);
                echo json_encode($response, JSON_PRETTY_PRINT);
            } else {
                throw new Exception('Method not allowed', 405);
            }
            break;

        case 'verify-slip':
            // POST /api/verify-slip - Slip verification endpoint (placeholder)
            if ($request_method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON input', 400);
                }

                $response = [
                    'status' => 'success',
                    'message' => 'Slip verification endpoint ready',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'data' => [
                        'note' => 'This is a placeholder endpoint. OCR processing will be implemented here.',
                        'received_data' => $input
                    ]
                ];
                http_response_code(200);
                echo json_encode($response, JSON_PRETTY_PRINT);
            } else {
                throw new Exception('Method not allowed. Use POST.', 405);
            }
            break;

        case 'parse-slip':
            // POST /api/parse-slip - Slip parsing endpoint (placeholder)
            if ($request_method === 'POST') {
                $response = [
                    'status' => 'success',
                    'message' => 'Slip parsing endpoint ready',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'data' => [
                        'note' => 'This is a placeholder endpoint. Image processing will be implemented here.',
                        'supported_formats' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                        'max_file_size' => '10MB'
                    ]
                ];
                http_response_code(200);
                echo json_encode($response, JSON_PRETTY_PRINT);
            } else {
                throw new Exception('Method not allowed. Use POST.', 405);
            }
            break;

        case 'ocr':
            // POST /api/ocr - OCR processing endpoint
            if ($request_method === 'POST') {
                // Include the OCR processing logic
                require_once 'ocr.php';
                exit(); // Exit to prevent further processing
            } else {
                throw new Exception('Method not allowed. Use POST.', 405);
            }
            break;

        case 'tesseract-test':
            // GET /api/tesseract-test - Test Tesseract OCR installation
            if ($request_method === 'GET') {
                // Include the Tesseract test logic
                require_once 'tesseract-test.php';
                exit(); // Exit to prevent further processing
            } else {
                throw new Exception('Method not allowed. Use GET.', 405);
            }
            break;

        default:
            // Root endpoint or 404
            if ($endpoint === '' || $endpoint === 'api') {
                // GET /api/ - API information
                if ($request_method === 'GET') {
                    $response = [
                        'status' => 'success',
                        'message' => 'Bank Slip Reader API',
                        'timestamp' => date('Y-m-d H:i:s'),
                        'version' => '1.0.0',
                        'description' => 'Backend API for Bank Slip Reader application',
                        'endpoints' => [
                            'GET /api/test' => 'Test endpoint - returns hello world',
                            'GET /api/health' => 'Health check endpoint',
                            'POST /api/verify-slip' => 'Verify slip data (JSON input)',
                            'POST /api/parse-slip' => 'Parse slip image (multipart/form-data)',
                            'POST /api/ocr' => 'Process image with OCR and extract slip data'
                        ],
                        'documentation' => 'See README.md for full API documentation'
                    ];
                    http_response_code(200);
                    echo json_encode($response, JSON_PRETTY_PRINT);
                } else {
                    throw new Exception('Method not allowed', 405);
                }
            } else {
                throw new Exception('Endpoint not found', 404);
            }
            break;
    }
} catch (Exception $e) {
    $error_response = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
        'path' => $request_uri,
        'method' => $request_method
    ];

    http_response_code($e->getCode() ?: 500);
    echo json_encode($error_response, JSON_PRETTY_PRINT);
}
?>
