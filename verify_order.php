<?php
/**
 * Script to verify an order in the database
 * Usage: Access this script through a web browser with ?order_id=YOUR_ORDER_ID
 */

// Start session and include required files
session_start();
require_once __DIR__ . '/config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Function to send JSON response
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode(array_merge([
        'success' => $statusCode >= 200 && $statusCode < 300,
        'timestamp' => date('Y-m-d H:i:s')
    ], $data), JSON_PRETTY_PRINT);
    exit;
}

// Check if order_id is provided
if (!isset($_GET['order_id']) || empty(trim($_GET['order_id']))) {
    sendResponse([
        'message' => 'Please provide an order ID using ?order_id=YOUR_ORDER_ID in the URL.',
        'example' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?order_id=123',
        'error_code' => 'missing_order_id'
    ], 400);
}

$order_id = trim($_GET['order_id']);

// Initialize response array
$response = [
    'request' => [
        'order_id' => $order_id,
        'server_time' => date('Y-m-d H:i:s')
    ]
];

try {
    // Get database connection
    global $conn;
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Check if order ID matches ORD{number} format
    $numeric_id = $order_id;
    if (preg_match('/^ORD(\d+)$/i', $order_id, $matches)) {
        $numeric_id = $matches[1];
    }
    
    // Query to get order details - using minimal schema
    $query = "SELECT id, status, user_id FROM orders WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    // Convert order_id to integer to match the database type
    $order_id_int = (int)$numeric_id;
    $stmt->bind_param('i', $order_id_int);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Check for similar order IDs
        $search_term = "%$order_id%";
        $similar_orders = [];
        
        // Try to find similar orders
        $similar_query = "SELECT id FROM orders WHERE id LIKE ? LIMIT 5";
        if ($stmt2 = $conn->prepare($similar_query)) {
            $stmt2->bind_param('s', $search_term);
            if ($stmt2->execute()) {
                $similar_result = $stmt2->get_result();
                if ($similar_result) {
                    $similar_orders = $similar_result->fetch_all(MYSQLI_ASSOC);
                }
            }
        }
        
        sendResponse([
            'success' => false,
            'message' => 'Order not found',
            'error_code' => 'order_not_found',
            'suggestions' => !empty($similar_orders) ? $similar_orders : null,
            'debug' => [
                'query' => $query,
                'params' => [$order_id_int],
                'similar_query' => isset($similar_query) ? $similar_query : null,
                'similar_params' => [$search_term]
            ]
        ], 404);
    }
    
    $order = $result->fetch_assoc();
    
    // Get order items if the table exists
    $items = [];
    try {
        $stmt_items = $conn->prepare("SHOW TABLES LIKE 'order_items'");
        if ($stmt_items && $stmt_items->execute() && $stmt_items->get_result()->num_rows > 0) {
            $stmt_items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
            if ($stmt_items) {
                $stmt_items->bind_param('i', $order['id']);
                if ($stmt_items->execute()) {
                    $items_result = $stmt_items->get_result();
                    if ($items_result) {
                        $items = $items_result->fetch_all(MYSQLI_ASSOC);
                    }
                }
            }
        }
    } catch (Exception $e) {
        // If there's an error, just continue with empty items
        $items = [];
    }
    
    // Get order status history if the table exists
    $history = [];
    try {
        $stmt_history = $conn->prepare("SHOW TABLES LIKE 'order_status_history'");
        if ($stmt_history && $stmt_history->execute() && $stmt_history->get_result()->num_rows > 0) {
            $stmt_history = $conn->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at DESC");
            if ($stmt_history) {
                $stmt_history->bind_param('i', $order['id']);
                if ($stmt_history->execute()) {
                    $history_result = $stmt_history->get_result();
                    if ($history_result) {
                        $history = $history_result->fetch_all(MYSQLI_ASSOC);
                    }
                }
            }
        }
    } catch (Exception $e) {
        // If there's an error, just continue with empty history
        $history = [];
    }
    
    // Prepare response with available data
    $response['order'] = [
        'id' => $order['id'],
        'order_number' => $order['id'], // Using ID as order number
        'status' => $order['status'],
        'item_count' => count($items),
        'user' => [
            'id' => $order['user_id']
        ],
        'items' => $items,
        'status_history' => $history,
        'can_cancel' => in_array(strtolower($order['status']), ['pending', 'confirmed', 'processing', 'new']),
        'cancellation_notes' => in_array(strtolower($order['status']), ['pending', 'confirmed', 'processing', 'new']) 
            ? 'This order can be cancelled.' 
            : 'This order cannot be cancelled because its status is: ' . $order['status']
    ];
    
    sendResponse($response);
    
} catch (Exception $e) {
    error_log('Error in verify_order.php: ' . $e->getMessage());
    sendResponse([
        'message' => 'An error occurred while processing your request.',
        'error' => $e->getMessage(),
        'error_code' => 'server_error'
    ], 500);
}
