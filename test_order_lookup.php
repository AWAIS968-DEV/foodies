<?php
// Test script to verify order lookup
require_once 'config/database.php';
require_once 'includes/functions.php';

// Start session
session_start();

// Set content type
header('Content-Type: text/plain');

echo "=== Order Lookup Test ===\n\n";

// Get all orders from the database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset('utf8mb4');

// Get all orders
$sql = "SELECT id, user_id, status, order_date FROM orders ORDER BY id DESC LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " orders in the database.\n\n";
    
    // Test lookup for each order
    while($order = $result->fetch_assoc()) {
        echo "Testing order ID: " . $order['id'] . "\n";
        echo "- User ID: " . $order['user_id'] . "\n";
        echo "- Status: " . $order['status'] . "\n";
        
        // Test the exact query from cancel_order.php
        $stmt = $conn->prepare("SELECT id, status, user_id FROM orders WHERE id = ?");
        $stmt->bind_param('i', $order['id']);
        $stmt->execute();
        $testResult = $stmt->get_result();
        
        if ($testResult->num_rows > 0) {
            echo "- Lookup SUCCESS\n\n";
        } else {
            echo "- Lookup FAILED\n\n";
        }
    }
} else {
    echo "No orders found in the database.\n";
}

// Test with a non-existent order ID
$nonExistentId = 999999;
$stmt = $conn->prepare("SELECT id, status, user_id FROM orders WHERE id = ?");
$stmt->bind_param('i', $nonExistentId);
$stmt->execute();
$testResult = $stmt->get_result();

echo "\nTesting non-existent order ID ($nonExistentId): ";
echo $testResult->num_rows > 0 ? "FOUND (This is unexpected!)\n" : "Not found (This is expected)\n";

$conn->close();
?>
