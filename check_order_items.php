<?php
// Check order_items table structure and content
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

    // Check if order_items table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'order_items'");
    
    if (!$tableCheck || $tableCheck->num_rows === 0) {
        sendResponse([
            'table_exists' => false,
            'message' => 'order_items table does not exist in the database.'
        ]);
    }

    // Get table structure
    $result = $conn->query("SHOW COLUMNS FROM order_items");
    if (!$result) {
        throw new Exception('Error getting table structure: ' . $conn->error);
    }
    
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row;
    }
    
    // Get sample data (first 5 rows)
    $sampleData = [];
    $sampleResult = $conn->query("SELECT * FROM order_items ORDER BY id LIMIT 5");
    if ($sampleResult) {
        while ($row = $sampleResult->fetch_assoc()) {
            $sampleData[] = $row;
        }
    }
    
    // Get row count
    $countResult = $conn->query("SELECT COUNT(*) as count FROM order_items");
    $rowCount = $countResult ? $countResult->fetch_assoc()['count'] : 0;
    
    sendResponse([
        'table_exists' => true,
        'table_structure' => $columns,
        'sample_data' => $sampleData,
        'total_items' => $rowCount
    ]);
    
} catch (Exception $e) {
    sendResponse([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}
