<?php
/**
 * update_delivered_orders.php
 * 
 * This script updates order status to 'delivered' when the estimated delivery time has passed.
 * It should be run periodically via a cron job (e.g., every 5-15 minutes).
 */

// Include database configuration
require_once __DIR__ . '/config/database.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function for debugging
function logMessage($message) {
    $logFile = __DIR__ . '/logs/order_updates.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    
    // Create logs directory if it doesn't exist
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage;
}

try {
    // Check database connection
    global $conn;
    if (!$conn) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }
    
    // Set charset
    $conn->set_charset('utf8mb4');
    
    // Get current timestamp
    $now = date('Y-m-d H:i:00'); // Round to nearest minute to avoid microsecond mismatches
    
    logMessage("Starting order status update check at $now");
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Find orders where:
        // 1. Status is not already 'delivered' or a terminal state
        // 2. delivery_date is in the past
        // 3. Status is one of the active statuses that can transition to 'delivered'
        $query = "
            SELECT id, order_number, status, delivery_date 
            FROM orders 
            WHERE status IN ('confirmed', 'preparing', 'out_for_delivery')
            AND delivery_date IS NOT NULL
            AND delivery_date <= ?
            ORDER BY delivery_date ASC
        ";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param('s', $now);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        $orderCount = count($orders);
        
        logMessage("Found $orderCount orders to update");
        
        $updatedCount = 0;
        
        foreach ($orders as $order) {
            $orderId = $order['id'];
            $orderNumber = $order['order_number'];
            $oldStatus = $order['status'];
            $deliveryDate = $order['delivery_date'];
            
            logMessage("Updating order #$orderNumber (ID: $orderId) from '$oldStatus' to 'delivered' (estimated: $deliveryDate)");
            
            // Update the order status to 'delivered'
            $updateStmt = $conn->prepare("UPDATE orders SET status = 'delivered', updated_at = NOW() WHERE id = ?");
            if (!$updateStmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            
            $updateStmt->bind_param('i', $orderId);
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
                ) VALUES (?, 'delivered', ?, NOW())
            ") or die($conn->error);
            
            $notes = "Status automatically updated to 'delivered' as the estimated delivery time ($deliveryDate) has passed";
            $historyStmt->bind_param('is', $orderId, $notes);
            if (!$historyStmt->execute()) {
                throw new Exception('Failed to add status history: ' . $historyStmt->error);
            }
            
            $updatedCount++;
        }
        
        // Commit transaction
        $conn->commit();
        
        logMessage("Successfully updated $updatedCount orders to 'delivered' status");
        echo "Successfully updated $updatedCount orders to 'delivered' status\n";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    $errorMessage = 'Error: ' . $e->getMessage();
    logMessage($errorMessage);
    die($errorMessage . "\n");
}

// Close connection
if (isset($conn)) {
    $conn->close();
}

logMessage("Order status update check completed at " . date('Y-m-d H:i:s') . "\n");
