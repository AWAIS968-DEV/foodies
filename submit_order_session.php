<?php
session_start();
header('Content-Type: application/json');

// Initialize orders array in session if not exists
if (!isset($_SESSION['orders'])) {
    $_SESSION['orders'] = [];
}

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit;
}

// Generate a simple order ID (timestamp + random number)
$order_id = 'ORD' . time() . rand(100, 999);
$order_date = date('Y-m-d H:i:s');

// Prepare order data
$order = [
    'id' => $order_id,
    'order_date' => $order_date,
    'customer_name' => $data['customer_name'] ?? 'Guest',
    'phone' => $data['phone'] ?? '',
    'address' => $data['address'] ?? '',
    'payment_method' => $data['payment_method'] ?? 'Cash on Delivery',
    'total_amount' => $data['total_amount'] ?? 0,
    'status' => 'Processing',
    'items' => []
];

// Add items to order
foreach (($data['items'] ?? []) as $item) {
    $order['items'][] = [
        'item_name' => $item['name'] ?? '',
        'quantity' => $item['quantity'] ?? 1,
        'price' => $item['price'] ?? 0,
        'total' => ($item['price'] ?? 0) * ($item['quantity'] ?? 1)
    ];
}

// Add order to session
$_SESSION['orders'][$order_id] = $order;

// Keep only the last 10 orders
if (count($_SESSION['orders']) > 10) {
    $_SESSION['orders'] = array_slice($_SESSION['orders'], -10, 10, true);
}

// Return success response
echo json_encode([
    'success' => true,
    'order_id' => $order_id,
    'redirect' => 'order-confirmation.html?order_id=' . $order_id
]);
