<?php
// create_test_order.php
require_once __DIR__ . '/config/database.php';

// Use the first available user
$user = $conn->query("SELECT id FROM users LIMIT 1")->fetch_assoc();
$user_id = $user['id'];

// Create a new order
$conn->query("INSERT INTO orders (user_id, status, total_amount, delivery_address, payment_method) 
             VALUES ($user_id, 'processing', 29.99, '123 Test St', 'cash_on_delivery')");

$order_id = $conn->insert_id;

// Add order items
$items = [
    [1, 2, 9.99],  // menu_item_id, quantity, price
    [2, 1, 4.99]
];

foreach ($items as $item) {
    $conn->query("INSERT INTO order_items (order_id, menu_item_id, quantity, price) 
                 VALUES ($order_id, $item[0], $item[1], $item[2])");
}

echo "Created new order #$order_id with status 'processing'";