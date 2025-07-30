<?php
session_start();
header('Content-Type: application/json');

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

$orderId = $_GET['order_id'];
$response = ['success' => false];

// Check if orders exist in session
if (isset($_SESSION['orders']) && is_array($_SESSION['orders'])) {
    // Find the requested order
    foreach ($_SESSION['orders'] as $order) {
        if ($order['id'] === $orderId) {
            $response = [
                'success' => true,
                'order' => $order
            ];
            break;
        }
    }
    
    if (!$response['success']) {
        $response['message'] = 'Order not found';
    }
} else {
    $response['message'] = 'No orders found';
}

echo json_encode($response);
