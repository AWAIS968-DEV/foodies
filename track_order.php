<?php
// Start session and include database connection
session_start();
require_once 'db_connection.php';

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch order details
$stmt = $conn->prepare("SELECT o.*, 
                       (SELECT SUM(quantity * price) FROM order_items WHERE order_id = o.order_id) as total_amount
                       FROM orders o 
                       WHERE o.order_id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If order not found or doesn't belong to user, redirect to dashboard
if (!$order) {
    $_SESSION['error'] = 'Order not found or access denied.';
    header('Location: dashboard.php');
    exit();
}

// Fetch order items
$stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.image 
                       FROM order_items oi 
                       LEFT JOIN products p ON oi.product_id = p.product_id 
                       WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Order status timeline
$status_timeline = [
    'pending' => ['icon' => 'clock', 'title' => 'Order Placed', 'description' => 'We have received your order'],
    'confirmed' => ['icon' => 'check-circle', 'title' => 'Order Confirmed', 'description' => 'Your order has been confirmed'],
    'processing' => ['icon' => 'utensils', 'title' => 'Preparing Your Food', 'description' => 'Our chef is working on your order'],
    'out_for_delivery' => ['icon' => 'motorcycle', 'title' => 'Out for Delivery', 'description' => 'Your food is on the way!'],
    'delivered' => ['icon' => 'check-circle', 'title' => 'Delivered', 'description' => 'Enjoy your meal!']
];

// Current status index for progress tracking
$statuses = array_keys($status_timeline);
$current_status_index = array_search($order['status'], $statuses);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #<?php echo $order_id; ?> - Foodies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Order #<?php echo $order_id; ?></li>
                    </ol>
                </nav>
                <h2 class="mb-0">Order #<?php echo $order_id; ?></h2>
                <p class="text-muted">Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <!-- Order Status Timeline -->
                        <div class="order-timeline">
                            <div class="timeline-progress">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?php echo (($current_status_index + 1) / count($statuses)) * 100; ?>%;">
                                    </div>
                                </div>
                            </div>
                            <div class="timeline-steps">
                                <?php foreach ($status_timeline as $status => $info): 
                                    $status_index = array_search($status, $statuses);
                                    $is_completed = $status_index <= $current_status_index;
                                    $is_current = $status_index === $current_status_index;
                                ?>
                                    <div class="timeline-step <?php echo $is_completed ? 'completed' : ''; ?> 
                                                           <?php echo $is_current ? 'current' : ''; ?>">
                                        <div class="timeline-icon">
                                            <i class="fas fa-<?php echo $info['icon']; ?>"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h6><?php echo $info['title']; ?></h6>
                                            <p class="small text-muted mb-0"><?php echo $info['description']; ?></p>
                                            <?php if ($is_current && $order['status_updated_at']): ?>
                                                <p class="small text-muted mt-1 mb-0">
                                                    <?php echo date('M j, g:i A', strtotime($order['status_updated_at'])); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($order_items as $item): ?>
                                <div class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                 class="img-fluid rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                        <?php endif; ?>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                            <p class="text-muted small mb-1">Quantity: <?php echo $item['quantity']; ?></p>
                                            <p class="mb-0">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Fee:</span>
                            <span>$<?php echo number_format($order['delivery_fee'] ?? 0, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span>$<?php echo number_format($order['tax_amount'] ?? 0, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span>$<?php echo number_format(($order['total_amount'] ?? 0) + ($order['delivery_fee'] ?? 0) + ($order['tax_amount'] ?? 0), 2); ?></span>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Delivery Address</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><?php echo htmlspecialchars($order['delivery_name']); ?></p>
                        <p class="mb-1"><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                        <p class="mb-1">
                            <?php 
                            $address_parts = [];
                            if (!empty($order['delivery_city'])) $address_parts[] = $order['delivery_city'];
                            if (!empty($order['delivery_state'])) $address_parts[] = $order['delivery_state'];
                            if (!empty($order['delivery_zip'])) $address_parts[] = $order['delivery_zip'];
                            if (!empty($order['delivery_country'])) $address_parts[] = $order['delivery_country'];
                            echo htmlspecialchars(implode(', ', $address_parts));
                            ?>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-phone-alt me-2"></i> 
                            <?php echo !empty($order['delivery_phone']) ? htmlspecialchars($order['delivery_phone']) : 'N/A'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
