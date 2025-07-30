<?php
// Script to add a test order to the database
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

    // Get the first user from the database to associate with this order
    $userResult = $conn->query("SELECT id FROM users LIMIT 1");
    if (!$userResult || $userResult->num_rows === 0) {
        throw new Exception('No users found in the database. Please create a user first.');
    }
    $user = $userResult->fetch_assoc();
    $userId = $user['id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert test order
        $stmt = $conn->prepare("
            INSERT INTO orders (
                user_id, 
                order_date, 
                total_amount, 
                status, 
                delivery_address, 
                payment_method
            ) VALUES (?, NOW(), ?, 'pending', ?, 'cash_on_delivery')
        ");

        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $deliveryAddress = '123 Test Street, Test City, 12345';
        $totalAmount = 29.99;
        
        $stmt->bind_param('ids', $userId, $totalAmount, $deliveryAddress);
        
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $orderId = $conn->insert_id;

        // Now let's add some order items if the order_items table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'order_items'");
        if ($tableCheck && $tableCheck->num_rows > 0) {
            $itemStmt = $conn->prepare("
                INSERT INTO order_items (
                    order_id, 
                    menu_item_id, 
                    quantity, 
                    price,
                    special_instructions
                ) VALUES (?, ?, ?, ?, ?)
            ") or die($conn->error);

            // Add some test items
            $testItems = [
                [1, 2, 9.99, 'No onions, please'],  // menu_item_id, quantity, price, special_instructions
                [2, 1, 4.99, 'Extra crispy'],
                [3, 1, 5.02, 'No ice']
            ];

            foreach ($testItems as $item) {
                $itemStmt->bind_param(
                    'iidss', 
                    $orderId, 
                    $item[0], // menu_item_id
                    $item[1], // quantity
                    $item[2], // price
                    $item[3]  // special_instructions
                );
                if (!$itemStmt->execute()) {
                    throw new Exception('Error inserting order items: ' . $itemStmt->error);
                }
            }
        }

        // Add order status history
        $conn->query("INSERT INTO order_status_history (order_id, status, notes) VALUES ($orderId, 'pending', 'Test order created')")
            or die('Error adding order status history: ' . $conn->error);

        // Commit transaction
        $conn->commit();

        sendResponse([
            'message' => 'Test order created successfully',
            'order_id' => $orderId,
            'user_id' => $userId,
            'total_amount' => $totalAmount,
            'delivery_address' => $deliveryAddress
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
