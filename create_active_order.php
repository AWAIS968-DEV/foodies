<?php
// Create a new test order with status 'Processing'
require_once 'config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type
header('Content-Type: text/plain');

echo "Creating a new test order...\n";

try {
    // Use the first available user
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    $conn->set_charset('utf8mb4');
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create a new order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, status, total_amount, delivery_address, payment_method) 
                              VALUES (?, 'Processing', 29.99, '123 Test St', 'cash_on_delivery')");
        $user_id = 3; // Using the same user_id as existing orders
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        $order_id = $conn->insert_id;
        
        // Add order items
        $items = [
            [1, 2, 9.99], // menu_item_id, quantity, price
            [2, 1, 4.99]
        ];
        
        $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) 
                                  VALUES (?, ?, ?, ?)");
        
        foreach ($items as $item) {
            $itemStmt->bind_param('iiid', $order_id, $item[0], $item[1], $item[2]);
            $itemStmt->execute();
        }
        
        // Add status history
        $historyStmt = $conn->prepare("INSERT INTO order_status_history 
                                      (order_id, status, notes, created_by, created_at) 
                                      VALUES (?, 'Processing', 'Test order created', ?, NOW())");
        $historyStmt->bind_param('ii', $order_id, $user_id);
        $historyStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo "Successfully created test order #$order_id with status 'Processing'\n";
        echo "You can now try to cancel this order.\n";
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (isset($conn)) {
        echo "MySQL Error: " . $conn->error . "\n";
    }
}
?>
