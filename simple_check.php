<?php
// Simple error checking script
header('Content-Type: text/plain');

echo "=== Simple Error Check ===\n\n";

// 1. Basic PHP info
echo "PHP Version: " . phpversion() . "\n";
echo "Error Reporting: " . ini_get('error_reporting') . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n";
echo "Error Log: " . ini_get('error_log') . "\n\n";

// 2. Check database connection
echo "=== Database Connection Test ===\n";
require_once __DIR__ . '/config/database.php';

global $conn;

if ($conn) {
    echo "✅ Database connection successful\n";
    
    // Check if tables exist
    $tables = ['orders', 'order_items', 'order_status_history'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        echo ($result && $result->num_rows > 0) ? "✅ Table '$table' exists\n" : "❌ Table '$table' does NOT exist\n";
    }
    
    // Check order status
    $order_id = 2;
    $result = $conn->query("SELECT id, status FROM orders WHERE id = $order_id");
    if ($result && $result->num_rows > 0) {
        $order = $result->fetch_assoc();
        echo "\nOrder #$order_id found. Current status: '{$order['status']}'\n";
    } else {
        echo "\n❌ Order #$order_id not found\n";
    }
    
} else {
    echo "❌ Database connection failed: " . mysqli_connect_error() . "\n";
}

// 3. Check last error
echo "\n=== Last Error ===\n";
$lastError = error_get_last();
if ($lastError) {
    print_r($lastError);
} else {
    echo "No recent errors.\n";
}

// 4. Check if we can write to the directory
echo "\n=== Directory Permissions ===\n";
$testFile = __DIR__ . '/test_write.txt';
if (file_put_contents($testFile, 'test') !== false) {
    echo "✅ Can write to directory\n";
    unlink($testFile); // Clean up
} else {
    echo "❌ Cannot write to directory\n";
}
