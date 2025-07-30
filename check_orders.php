<?php
// Check orders table structure and content
header('Content-Type: application/json');

// Include database configuration
require_once __DIR__ . '/config/database.php';

// Function to send JSON response
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode(array_merge([
        'success' => $statusCode >= 200 && $statusCode < 300,
        'timestamp' => date('Y-m-d H:i:s')
    ], $data), JSON_PRETTY_PRINT);
    exit;
}

try {
    // Check database connection
    global $conn;
    if (!$conn) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }

    // Get table structure
    $result = $conn->query("SHOW COLUMNS FROM orders");
    if (!$result) {
        throw new Exception('Error getting table structure: ' . $conn->error);
    }
    
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row;
    }
    
    // Get sample data (first 5 rows)
    $sampleData = [];
    $sampleResult = $conn->query("SELECT * FROM orders ORDER BY id LIMIT 5");
    if ($sampleResult) {
        while ($row = $sampleResult->fetch_assoc()) {
            $sampleData[] = $row;
        }
    }
    
    // Get row count
    $countResult = $conn->query("SELECT COUNT(*) as count FROM orders");
    $rowCount = $countResult ? $countResult->fetch_assoc()['count'] : 0;
    
    sendResponse([
        'table_structure' => $columns,
        'sample_data' => $sampleData,
        'total_orders' => $rowCount,
        'database' => $conn->query("SELECT DATABASE() as db")->fetch_assoc()['db']
    ]);
    
} catch (Exception $e) {
    sendResponse([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}
