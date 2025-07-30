<?php
// Test endpoint to check server configuration and PHP processing
header('Content-Type: application/json');

try {
    // Basic test response
    $response = [
        'success' => true,
        'message' => 'Test endpoint is working!',
        'server' => [
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Not available',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Not available',
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'Not set'
        ],
        'session' => [
            'session_status' => session_status(),
            'session_id' => session_id()
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Handle any errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}
