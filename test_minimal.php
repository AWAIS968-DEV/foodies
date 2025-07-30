<?php
// Disable all error output to browser
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

// Set headers to prevent caching
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

// Function to send JSON response
function send_json($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Test 1: Check for output before headers
if (headers_sent($filename, $linenum)) {
    error_log("Headers already sent in $filename on line $linenum");
    send_json(['error' => 'Headers already sent'], 500);
}

// Test 2: Check session
session_start();

// Test 3: Check database connection
$db_connected = false;
try {
    require_once 'db_connection.php';
    if (isset($conn) && $conn instanceof mysqli) {
        if ($conn->ping()) {
            $db_connected = true;
        }
    }
} catch (Exception $e) {
    error_log('Database connection error: ' . $e->getMessage());
}

// Test 4: Check if we can query the database
$test_query = false;
if ($db_connected) {
    try {
        $result = $conn->query("SELECT 1 AS test");
        if ($result && $result->num_rows > 0) {
            $test_query = true;
        }
    } catch (Exception $e) {
        error_log('Test query failed: ' . $e->getMessage());
    }
}

// Send success response
send_json([
    'success' => true,
    'tests' => [
        'headers_sent' => headers_sent(),
        'session_started' => session_status() === PHP_SESSION_ACTIVE,
        'database_connected' => $db_connected,
        'test_query' => $test_query,
        'php_version' => phpversion(),
        'session_id' => session_id()
    ]
]);
