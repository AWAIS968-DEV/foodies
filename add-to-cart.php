<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'cart_count' => 0,
    'cart_total' => 0.00,
    'item_count' => 0
];

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($input['action'])) {
        throw new Exception('Action is required');
    }
    
    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [
            'items' => [],
            'total_items' => 0,
            'subtotal' => 0.00,
            'tax' => 0.00,
            'delivery_fee' => 0.00,
            'total' => 0.00
        ];
    }
    
    // Handle different actions
    switch ($input['action']) {
        case 'add':
            // Validate required fields for add action
            if (!isset($input['id'], $input['name'], $input['price'], $input['quantity'])) {
                throw new Exception('Missing required fields for add action');
            }
            
            $item_id = (int)$input['id'];
            $quantity = (int)$input['quantity'];
            $price = (float)$input['price'];
            $name = trim($input['name']);
            $image = $input['image'] ?? '';
            $special_requests = $input['special_requests'] ?? '';
            $modifiers = $input['modifiers'] ?? [];
            
            // Validate quantity
            if ($quantity < 1) {
                throw new Exception('Quantity must be at least 1');
            }
            
            // Calculate modifiers total
            $modifiers_total = 0;
            foreach ($modifiers as $modifier) {
                $modifiers_total += (float)($modifier['price'] ?? 0);
            }
            
            // Calculate item total price
            $item_total_price = ($price + $modifiers_total) * $quantity;
            
            // Create or update cart item
            if (isset($_SESSION['cart']['items'][$item_id])) {
                // Update existing item
                $_SESSION['cart']['items'][$item_id]['quantity'] += $quantity;
            } else {
                // Add new item
                $_SESSION['cart']['items'][$item_id] = [
                    'id' => $item_id,
                    'name' => $name,
                    'price' => $price,
                    'quantity' => $quantity,
                    'image' => $image,
                    'special_requests' => $special_requests,
                    'modifiers' => $modifiers,
                    'modifiers_total' => $modifiers_total,
                    'item_total' => $item_total_price
                ];
            }
            
            $response['message'] = 'Item added to cart';
            break;
            
        case 'update':
            // Validate required fields for update action
            if (!isset($input['id'], $input['quantity'])) {
                throw new Exception('Missing required fields for update action');
            }
            
            $item_id = (int)$input['id'];
            $quantity = (int)$input['quantity'];
            
            // Validate quantity
            if ($quantity < 0) {
                throw new Exception('Quantity cannot be negative');
            }
            
            if (isset($_SESSION['cart']['items'][$item_id])) {
                if ($quantity === 0) {
                    // Remove item if quantity is 0
                    unset($_SESSION['cart']['items'][$item_id]);
                    $response['message'] = 'Item removed from cart';
                } else {
                    // Update quantity
                    $_SESSION['cart']['items'][$item_id]['quantity'] = $quantity;
                    $response['message'] = 'Cart updated';
                }
            } else {
                throw new Exception('Item not found in cart');
            }
            break;
            
        case 'remove':
            // Validate required fields for remove action
            if (!isset($input['id'])) {
                throw new Exception('Item ID is required for remove action');
            }
            
            $item_id = (int)$input['id'];
            
            if (isset($_SESSION['cart']['items'][$item_id])) {
                // Remove item from cart
                unset($_SESSION['cart']['items'][$item_id]);
                $response['message'] = 'Item removed from cart';
            } else {
                throw new Exception('Item not found in cart');
            }
            break;
            
        case 'clear':
            // Clear the entire cart
            $_SESSION['cart'] = [
                'items' => [],
                'total_items' => 0,
                'subtotal' => 0.00,
                'tax' => 0.00,
                'delivery_fee' => 0.00,
                'total' => 0.00
            ];
            $response['message'] = 'Cart cleared';
            break;
            
        case 'get':
            // Just return the current cart
            $response['message'] = 'Cart retrieved';
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
    // Update cart totals
    updateCartTotals();
    
    // Set success response
    $response['success'] = true;
    $response['cart_count'] = count($_SESSION['cart']['items']);
    $response['cart_total'] = number_format($_SESSION['cart']['total'], 2);
    $response['item_count'] = $_SESSION['cart']['total_items'];
    $response['cart'] = $_SESSION['cart'];
    
} catch (Exception $e) {
    // Set error response
    $response['message'] = $e->getMessage();
    http_response_code(400); // Bad Request
}

// Function to update cart totals
function updateCartTotals() {
    $subtotal = 0;
    $total_items = 0;
    
    foreach ($_SESSION['cart']['items'] as &$item) {
        // Recalculate item total
        $item['item_total'] = ($item['price'] + $item['modifiers_total']) * $item['quantity'];
        
        $subtotal += $item['item_total'];
        $total_items += $item['quantity'];
    }
    
    // Calculate tax (10% of subtotal)
    $tax_rate = 0.10;
    $tax = $subtotal * $tax_rate;
    
    // Calculate delivery fee ($5.00 for orders under $50, free otherwise)
    $delivery_fee = ($subtotal < 50.00 && $subtotal > 0) ? 5.00 : 0.00;
    
    // Calculate total
    $total = $subtotal + $tax + $delivery_fee;
    
    // Update cart totals
    $_SESSION['cart']['total_items'] = $total_items;
    $_SESSION['cart']['subtotal'] = $subtotal;
    $_SESSION['cart']['tax'] = $tax;
    $_SESSION['cart']['delivery_fee'] = $delivery_fee;
    $_SESSION['cart']['total'] = $total;
}

// Return JSON response
echo json_encode($response);
?>
