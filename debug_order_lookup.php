<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Set JSON header
header('Content-Type: application/json');

// Function to send JSON response
function send_json($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    send_json(['success' => false, 'message' => 'Not logged in'], 401);
}

// Get order ID from query string
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$user_id = (int)$_SESSION['user_id'];

// Include database connection
require_once 'db_connection.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    send_json(['success' => false, 'message' => 'Database connection failed'], 500);
}

try {
    // Debug info
    $debug = [
        'user_id' => $user_id,
        'order_id' => $order_id,
        'tables' => [],
        'order_data' => null,
        'user_orders' => []
    ];

    // Check if orders table exists and get its structure
    $result = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($result->num_rows === 0) {
        $debug['tables'][] = 'Orders table does not exist';
    } else {
        $debug['tables'][] = 'Orders table exists';
        
        // Get table structure
        $structure = $conn->query("DESCRIBE orders");
        $debug['table_structure'] = [];
        while ($row = $structure->fetch_assoc()) {
            $debug['table_structure'][] = $row;
        }
        
        // Try to find the order
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $debug['order_data'] = $result->fetch_assoc();
            } else {
                $debug['order_data'] = 'Order not found in database';
            }
        } else {
            $debug['order_data'] = 'Failed to prepare order lookup statement: ' . $conn->error;
        }
        
        // Find all orders for this user
        $user_orders = $conn->prepare("SELECT id, status, user_id FROM orders WHERE user_id = ?");
        if ($user_orders) {
            $user_orders->bind_param("i", $user_id);
            $user_orders->execute();
            $result = $user_orders->get_result();
            $debug['user_orders'] = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $debug['user_orders'] = 'Failed to prepare user orders statement: ' . $conn->error;
        }
    }
    
    // Check order_status_history table
    $result = $conn->query("SHOW TABLES LIKE 'order_status_history'");
    if ($result->num_rows === 0) {
        $debug['tables'][] = 'Order status history table does not exist';
    } else {
        $debug['tables'][] = 'Order status history table exists';
    }
    
    send_json([
        'success' => true,
        'debug' => $debug
    ]);
    
} catch (Exception $e) {
    send_json([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}
