<?php
// fix_order_status.php
require_once __DIR__ . '/config/database.php';

// Get the most recent order
$order = $conn->query("SELECT id, status FROM orders ORDER BY id DESC LIMIT 1")->fetch_assoc();

if ($order) {
    // Update status to 'Processing' (with capital P)
    $conn->query("UPDATE orders SET status = 'Processing' WHERE id = " . $order['id']);
    echo "Updated order #{$order['id']} status from '{$order['status']}' to 'Processing'";
} else {
    echo "No orders found";
}