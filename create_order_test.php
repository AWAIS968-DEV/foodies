<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Set content type
header('Content-Type: text/plain');

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get the first available user
    $user = $conn->query("SELECT id FROM users LIMIT 1")->fetch_assoc();
    if (!$user) {
        throw new Exception("No users found in the database");
    }
    
    $user_id = $user['id'];
    
    // Create a new order with status 'Processing' (cancellable status)
    $stmt = $conn->prepare("INSERT INTO orders (user_id, status, total_amount, delivery_address, payment_method) 
                          VALUES (?, 'Processing', 29.99, '123 Test St', 'cash_on_delivery')");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    
    $order_id = $conn->insert_id;
    
    // Add order items
    $items = [
        [1, 2, 9.99],  // menu_item_id, quantity, price
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
    echo "You can now try to cancel this order using the test page.\n";
    echo "<a href='test_cancel.html'>Go to Test Cancel Page</a>";
    
} catch (Exception $e) {
    // Rollback transaction if there was an error
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    echo "Error: " . $e->getMessage() . "\n";
    if (isset($conn)) {
        echo "MySQL Error: " . $conn->error . "\n";
    }
}
?>
