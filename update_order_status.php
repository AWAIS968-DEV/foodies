<?php
// Script to update order status for testing
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
    // Get order ID from query string
    $order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
    $new_status = isset($_GET['status']) ? $_GET['status'] : 'New';
    
    if ($order_id <= 0) {
        sendResponse([
            'success' => false,
            'message' => 'Invalid order ID'
        ], 400);
    }
    
    // Check database connection
    global $conn;
    if (!$conn) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }
    
    // Set charset
    $conn->set_charset('utf8mb4');
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Verify the order exists
        $stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param('i', $order_id);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            sendResponse([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        
        $order = $result->fetch_assoc();
        $old_status = $order['status'];
        
        // Update the order status
        $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if (!$updateStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $updateStmt->bind_param('si', $new_status, $order_id);
        if (!$updateStmt->execute()) {
            throw new Exception('Update failed: ' . $updateStmt->error);
        }
        
        // Add status history
        $historyStmt = $conn->prepare("
            INSERT INTO order_status_history (
                order_id, 
                status, 
                notes, 
                created_at
            ) VALUES (?, ?, ?, NOW())
        ") or die($conn->error);
        
        $notes = "Status changed from {$old_status} to {$new_status} for testing";
        $historyStmt->bind_param('iss', $order_id, $new_status, $notes);
        $historyStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        sendResponse([
            'success' => true,
            'message' => 'Order status updated successfully',
            'order_id' => $order_id,
            'old_status' => $old_status,
            'new_status' => $new_status
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    sendResponse([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}
