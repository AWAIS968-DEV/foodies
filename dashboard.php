<?php
// Start session
session_start();

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

// Initialize orders array in session if not exists
if (!isset($_SESSION['orders'])) {
    $_SESSION['orders'] = [];
}

// Fetch user details from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone'] ?? '';

// Get orders from session
$active_orders = [];
$all_orders = [];

// Define what statuses are considered active
$active_statuses = ['Processing', 'Preparing', 'Out for Delivery'];

// Process session orders
foreach ($_SESSION['orders'] as $order) {
    // Add to all orders
    $all_orders[] = $order;
    
    // Add to active orders if status is in active statuses
    if (in_array($order['status'], $active_statuses)) {
        $active_orders[] = $order;
    }
}

// Sort all orders by date (newest first)
usort($all_orders, function($a, $b) {
    return strtotime($b['order_date']) - strtotime($a['order_date']);
});

// Sort active orders by date (newest first)
usort($active_orders, function($a, $b) {
    return strtotime($b['order_date']) - strtotime($a['order_date']);
});

// Limit to 5 active and recent orders for display
$active_orders = array_slice($active_orders, 0, 5);
$all_orders = array_slice($all_orders, 0, 5);

// No debug information in production
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Foodies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
    <!-- Animation CSS -->
    <link rel="stylesheet" href="css/dashboard-animations.css">
    <link rel="stylesheet" href="css/animations.css">
    <style>
        /* Modal styles */
        .modal {
            z-index: 1050;
            display: none;
            overflow: hidden;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            outline: 0;
        }
        
        .modal-dialog {
            margin: 1.75rem auto;
            position: relative;
            width: auto;
            max-width: 800px;
        }
        
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            width: 100vw;
            height: 100vh;
            background-color: #000;
        }
        
        .modal.fade.show {
            display: block;
            padding-right: 15px;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .modal-dialog {
            position: relative;
            width: auto;
            margin: 0.5rem;
            pointer-events: none;
            max-width: 800px;
            margin: 1.75rem auto;
        }
        
        .modal-content {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            pointer-events: auto;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 0.3rem;
            outline: 0;
        }
        
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            width: 100vw;
            height: 100vh;
            background-color: #000;
        }
        
        .modal-backdrop.fade {
            opacity: 0;
        }
        
        .modal-backdrop.show {
            opacity: 0.5;
        }
        
        /* Ensure all content is immediately visible */
        body * {
            opacity: 1 !important;
            transform: none !important;
            animation: none !important;
            transition: none !important;
        }
        
        /* Active orders section - ensure it's always visible */
        #active-orders-section {
            opacity: 1 !important;
            transform: none !important;
            visibility: visible !important;
            display: block !important;
        }
        
        /* Table styling for better visibility */
        .table {
            opacity: 1 !important;
            visibility: visible !important;
            margin-bottom: 0;
        }
        
        /* Custom header row styles */
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .table-header {
            display: flex;
            background-color: #f8f9fa;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table-header > div {
            padding: 0.5rem;
            font-weight: 600;
            color: #495057;
        }
        
        /* Responsive table styles */
        @media (max-width: 767.98px) {
            .table-content {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                -ms-overflow-style: -ms-autohiding-scrollbar;
            }
            
            .table {
                min-width: 600px;
            }
        }
        
        /* Order row styles */
        .order-row {
            transition: all 0.2s ease;
        }
        
        .order-row:hover {
            background-color: rgba(0, 0, 0, 0.02);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        /* Status badge styling */
        .status-badge {
            padding: 0.35em 0.65em;
            font-size: 0.85em;
            border-radius: 0.25rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        
        /* Action buttons */
        .btn-order-action {
            min-width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 0.65rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .order-row {
                flex-wrap: wrap;
                padding: 1rem;
                gap: 0.75rem;
            }
            
            .order-row > div {
                flex: 1 1 45% !important;
                padding: 0.25rem;
            }
            
            .order-row .d-flex {
                flex: 1 1 100% !important;
                justify-content: flex-start;
                margin-top: 0.5rem;
                padding-top: 0.5rem;
                border-top: 1px solid #eee;
            }
            
            /* Tracking panel styles */
            .tracking-panel {
                border: 1px solid #dee2e6;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
                transition: all 0.3s ease;
            }
            
            .tracking-steps {
                position: relative;
                padding: 1rem 0;
            }
            
            .tracking-step {
                position: relative;
                flex: 1;
                z-index: 1;
                opacity: 0.5;
                transition: all 0.3s ease;
                text-align: center;
                padding: 0 10px;
            }
            
            .tracking-step.active {
                opacity: 1;
            }
            
            .tracking-step.completed {
                opacity: 1;
            }
            
            .tracking-step.completed .step-icon {
                background: #198754;
                border-color: #198754;
                color: white;
            }
            
            .tracking-step .step-icon {
                width: 40px;
                height: 40px;
                margin: 0 auto 8px;
                border-radius: 50%;
                background: #f8f9fa;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 2px solid #dee2e6;
                color: #6c757d;
                transition: all 0.3s ease;
            }
            
            .tracking-step.active .step-icon {
                background: #0d6efd;
                border-color: #0d6efd;
                color: white;
            }
            
            .tracking-step .step-label {
                font-size: 0.8rem;
                margin-bottom: 4px;
                font-weight: 500;
            }
            
            .tracking-step .step-time {
                font-size: 0.75rem;
                color: #6c757d;
            }
            
            .tracking-steps {
                display: flex;
                justify-content: space-between;
                position: relative;
                padding-bottom: 20px;
                margin-bottom: 20px;
            }
            
            .tracking-steps::before {
                content: '';
                position: absolute;
                top: 20px;
                left: 0;
                right: 0;
                height: 2px;
                background: #dee2e6;
                z-index: 0;
            }
            
            .tracking-step.completed::after {
                content: '✓';
                position: absolute;
                top: -5px;
                right: 0;
                background: #198754;
                color: white;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container-fluid py-3 py-md-4 py-lg-5 px-3 px-md-4">
        <!-- Welcome Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <!-- Profile Image -->
                            <div class="me-4 position-relative">
                                <?php if (!empty($user['profile_image'])): ?>
                                    <img src="uploads/profile_images/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                         class="rounded-circle border border-3 border-white shadow-sm" 
                                         style="width: 80px; height: 80px; object-fit: cover;"
                                         alt="Profile">
                                <?php else: ?>
                                    <div class="bg-primary text-white d-flex align-items-center justify-content-center rounded-circle border border-3 border-white shadow-sm" 
                                         style="width: 80px; height: 80px; font-size: 2rem;">
                                        <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- User Info -->
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h2 class="mb-1 welcome-message">Welcome back, <?php echo !empty($username) ? htmlspecialchars($username) : 'Valued Customer'; ?>!</h2>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($email); ?></p>
                                            </div>
                                            <?php if (!empty($phone)): ?>
                                            <div class="col-md-6">
                                                <p class="mb-1"><i class="fas fa-phone me-2"></i> <?php echo htmlspecialchars($phone); ?></p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <a href="profile.php" class="btn btn-outline-primary">
                                        <i class="fas fa-user-edit me-2"></i>Edit Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Orders Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Active Orders</h4>
                        <a href="view_orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($active_orders)): ?>
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-shopping-bag fa-3x mb-3"></i>
                                <p class="mb-0">You don't have any active orders.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <!-- Mobile Table Header (hidden on desktop) -->
                                <div class="d-md-none small text-muted mb-2 px-3 pt-3">Swipe left to see more →</div>
                                
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 120px;">Order ID</th>
                                            <th style="min-width: 150px;">Date</th>
                                            <th style="width: 100px;">Items</th>
                                            <th style="width: 120px;">Total</th>
                                            <th style="width: 150px;">Status</th>
                                            <th style="width: 180px;" class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if (!empty($active_orders)): 
                                            foreach ($active_orders as $order): 
                                                $order_date = new DateTime($order['order_date']);
                                                $status_info = [
                                                    'Processing' => ['class' => 'bg-warning', 'icon' => 'fa-hourglass-half', 'tooltip' => 'Your order is being processed'],
                                                    'Preparing' => ['class' => 'bg-info', 'icon' => 'fa-utensils', 'tooltip' => 'Your food is being prepared'],
                                                    'Out for Delivery' => ['class' => 'bg-primary', 'icon' => 'fa-truck', 'tooltip' => 'Your order is on the way'],
                                                    'Delivered' => ['class' => 'bg-success', 'icon' => 'fa-check-circle', 'tooltip' => 'Order delivered successfully'],
                                                    'Cancelled' => ['class' => 'bg-danger', 'icon' => 'fa-times-circle', 'tooltip' => 'Order has been cancelled']
                                                ][$order['status']] ?? ['class' => 'bg-secondary', 'icon' => 'fa-question-circle', 'tooltip' => 'Status unknown'];
                                                
                                                // Calculate total items
                                                $total_items = 0;
                                                if (!empty($order['items'])) {
                                                    foreach ($order['items'] as $item) {
                                                        $total_items += ($item['quantity'] ?? 1);
                                                    }
                                                }
                                        ?>
                                        <tr class="order-row" data-order-id="<?php echo $order['id']; ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="d-md-none fw-bold me-2">Order ID:</span>
                                                    #<?php echo htmlspecialchars($order['id']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="d-md-none fw-bold me-2">Date:</span>
                                                    <?php echo $order_date->format('M d, Y h:i A'); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="d-md-none fw-bold me-2">Items:</span>
                                                    <?php echo $total_items; ?> item<?php echo $total_items != 1 ? 's' : ''; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center fw-medium">
                                                    <span class="d-md-none fw-bold me-2">Total:</span>
                                                    Rs <?php echo number_format($order['total_amount'] ?? 0, 2); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $status_info['class']; ?> text-white" 
                                                      data-bs-toggle="tooltip" 
                                                      title="<?php echo htmlspecialchars($status_info['tooltip']); ?>">
                                                    <i class="fas <?php echo $status_info['icon']; ?> me-1"></i>
                                                    <?php echo htmlspecialchars($order['status']); ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button class="btn btn-sm btn-outline-primary track-order" data-order-id="<?php echo $order['id']; ?>">
                                                        <i class="fas fa-truck me-1"></i> Track
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php 
                                            endforeach;
                                        else: 
                                        ?>
                                        <tr>
                                            <td colspan="6" class="text-center p-5 text-muted">
                                                <i class="fas fa-shopping-bag fa-3x mb-3"></i>
                                                <p class="mb-2">You don't have any active orders.</p>
                                                <a href="menu.php" class="btn btn-primary">Order Now</a>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- Tracking Modal -->
    <div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="trackingModalLabel">Order Tracking</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h4 id="current-status" class="text-primary mb-2">Loading...</h4>
                        <p id="status-message" class="text-muted">
                            <i class="fas fa-spinner fa-spin me-2"></i> Loading order status...
                        </p>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="text-center">
                        <span id="total-eta" class="badge bg-primary p-2 mb-3">ETA: Calculating...</span>
                    </div>
                    <div class="tracking-details">
                        <!-- Tracking steps will be added here by JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="refresh-tracking">
                        <i class="fas fa-sync-alt me-1"></i> Refresh Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Summary Modal -->
    <div class="modal fade" id="orderSummaryModal" tabindex="-1" aria-labelledby="orderSummaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="orderSummaryModalLabel">Order Summary</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="order-header mb-3">
                        <h6 class="mb-1">Order #<span id="order-id"></span></h6>
                        <small class="text-muted" id="order-date"></small>
                    </div>
                    
                    <div class="order-items mb-3">
                        <h6 class="border-bottom pb-2 mb-3">Items</h6>
                        <div id="order-items-list">
                            <!-- Order items will be added here by JavaScript -->
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-summary">
                        <h6 class="border-bottom pb-2 mb-3">Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span id="subtotal">Rs. 0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Fee:</span>
                            <span>Rs. 200</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold mt-3 pt-2 border-top">
                            <span>Total:</span>
                            <span id="order-total">Rs. 0</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="track-this-order">
                        <i class="fas fa-truck me-1"></i> Track Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Summary Modal -->
    <div class="modal fade" id="orderSummaryModal" tabindex="-1" aria-labelledby="orderSummaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="orderSummaryModalLabel">Order #<span id="modal-order-id"></span> - <span id="modal-order-date"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="order-items mb-4">
                        <h6 class="border-bottom pb-2 mb-3">Order Items</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-order-items-list">
                                    <!-- Order items will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="order-summary">
                        <h6 class="border-bottom pb-2 mb-3">Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span id="subtotal">Rs. 0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Fee:</span>
                            <span>Rs. 200</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold mt-3 pt-2 border-top">
                            <span>Total:</span>
                            <span id="order-total">Rs. 0</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="track-this-order">
                        <i class="fas fa-truck me-1"></i> Track Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Slide-in Order Details Panel -->
    <div id="orderDetailsPanel" class="order-details-panel">
        <div class="order-details-content">
            <div class="order-details-header">
                <h5>Order #<span id="order-id"></span> - Details</h5>
                <button type="button" class="btn-close" id="closeOrderDetails"></button>
            </div>
            <div class="order-details-body">
                <div class="order-info mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Order Date:</strong> <span id="order-date"></span></p>
                            <p class="mb-1"><strong>Status:</strong> <span id="order-status" class="badge bg-primary"></span></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-1"><strong>Delivery Address:</strong></p>
                            <p class="mb-1" id="delivery-address"></p>
                        </div>
                    </div>
                </div>
                
                <div class="order-items mb-3">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="order-items-list">
                                <!-- Order items will be loaded here -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                                    <td class="text-end" id="order-subtotal">Rs. 0.00</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Delivery Fee:</td>
                                    <td class="text-end" id="order-delivery-fee">Rs. 200.00</td>
                                </tr>
                                <tr class="table-active">
                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold" id="order-total">Rs. 0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="payment-info">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Payment Information</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Payment Method:</strong> <span id="payment-method"></span></p>
                            <p class="mb-0"><strong>Payment Status:</strong> <span id="payment-status" class="badge bg-success"></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="order-details-footer">
                <button type="button" class="btn btn-primary" id="track-this-order">
                    <i class="fas fa-truck me-1"></i> Track Order
                </button>
            </div>
        </div>
    </div>
    
    <!-- Overlay for when panel is open -->
    <div id="panelOverlay" class="panel-overlay"></div>
    
    <!-- Simple Test Modal -->
    <div class="modal fade" id="testModal" tabindex="-1" aria-labelledby="testModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="testModalLabel">Test Modal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>If you can see this, Bootstrap modals are working!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Debug script to check if Bootstrap loaded correctly -->
    <script>
        console.log('=== DEBUG SCRIPT START ===');
        console.log('jQuery version:', typeof jQuery !== 'undefined' ? jQuery.fn.jquery : 'jQuery not loaded');
        console.log('Bootstrap version:', typeof bootstrap !== 'undefined' ? 'Loaded' : 'Bootstrap not loaded');
        
        // Check if modal function exists
        console.log('Bootstrap Modal function:', typeof bootstrap?.Modal === 'function' ? 'Available' : 'Not available');
        
        // Check if orderSummaryModal element exists
        const modalElement = document.getElementById('orderSummaryModal');
        console.log('Order summary modal element exists:', !!modalElement);
        
        // Initialize slide-in panel
        const orderDetailsPanel = document.getElementById('orderDetailsPanel');
        const panelOverlay = document.getElementById('panelOverlay');
        const closeOrderDetailsBtn = document.getElementById('closeOrderDetails');
        
        // Add event delegation for View buttons
        document.addEventListener('click', function(e) {
            // Handle View button clicks
            if (e.target.closest('.view-order')) {
                e.preventDefault();
                e.stopPropagation();
                const orderId = e.target.closest('tr').dataset.orderId;
                console.log('View order clicked for:', orderId);
                if (window.showOrderDetails) {
                    window.showOrderDetails(orderId);
                }
                return false;
            }
        });
        
        // Function to show the order details panel
        window.showOrderDetails = function(orderId) {
            console.log('Showing order details for order:', orderId);
            
            // Add active class to show the panel and overlay
            orderDetailsPanel.classList.add('active');
            panelOverlay.classList.add('active');
            
            // Prevent body scroll when panel is open
            document.body.style.overflow = 'hidden';
            
            // Load order details (mock data for now)
            updateOrderSummaryPanel({
                id: orderId,
                status: 'Preparing',
                date: new Date().toLocaleDateString(),
                items: [
                    { name: 'Delicious Burger', quantity: 2, price: 1200 },
                    { name: 'French Fries', quantity: 1, price: 500 },
                    { name: 'Soft Drink', quantity: 2, price: 200 }
                ],
                subtotal: 2100,
                deliveryFee: 200,
                total: 2300,
                deliveryAddress: '123 Food Street, Cuisine City',
                paymentMethod: 'Credit Card',
                paymentStatus: 'Paid'
            });
        };
        
        // Function to hide the order details panel
        function hideOrderDetails() {
            console.log('Hiding order details panel');
            orderDetailsPanel.classList.remove('active');
            panelOverlay.classList.remove('active');
            document.body.style.overflow = ''; // Re-enable body scroll
        }
        
        // Close panel when clicking the close button
        if (closeOrderDetailsBtn) {
            closeOrderDetailsBtn.addEventListener('click', hideOrderDetails);
        }
        
        // Close panel when clicking the overlay
        if (panelOverlay) {
            panelOverlay.addEventListener('click', hideOrderDetails);
        }
        
        // Close panel when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && orderDetailsPanel.classList.contains('active')) {
                hideOrderDetails();
            }
        });
        
        // Function to update the order summary panel with data
        function updateOrderSummaryPanel(orderData) {
            console.log('Updating order summary panel with data:', orderData);
            
            // Update order header
            document.getElementById('order-id').textContent = orderData.id;
            document.getElementById('order-date').textContent = orderData.date;
            document.getElementById('order-status').textContent = orderData.status;
            document.getElementById('delivery-address').textContent = orderData.deliveryAddress;
            
            // Update order items
            const itemsList = document.getElementById('order-items-list');
            itemsList.innerHTML = ''; // Clear existing items
            
            orderData.items.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.name}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-end">Rs. ${(item.price / 100).toFixed(2)}</td>
                    <td class="text-end">Rs. ${(item.price * item.quantity / 100).toFixed(2)}</td>
                `;
                itemsList.appendChild(row);
            });
            
            // Update totals
            document.getElementById('order-subtotal').textContent = `Rs. ${(orderData.subtotal / 100).toFixed(2)}`;
            document.getElementById('order-delivery-fee').textContent = `Rs. ${(orderData.deliveryFee / 100).toFixed(2)}`;
            document.getElementById('order-total').textContent = `Rs. ${(orderData.total / 100).toFixed(2)}`;
            
            // Update payment info
            document.getElementById('payment-method').textContent = orderData.paymentMethod;
            document.getElementById('payment-status').textContent = orderData.paymentStatus;
            
            // Update status badge class
            const statusBadge = document.getElementById('order-status');
            statusBadge.className = 'badge';
            statusBadge.classList.add(getStatusBadgeClass(orderData.status));
            
            // Update payment status badge class
            const paymentStatusBadge = document.getElementById('payment-status');
            paymentStatusBadge.className = 'badge';
            paymentStatusBadge.classList.add(orderData.paymentStatus === 'Paid' ? 'bg-success' : 'bg-warning');
            
            // Set up track order button
            const trackBtn = document.getElementById('track-this-order');
            if (trackBtn) {
                trackBtn.onclick = function() {
                    hideOrderDetails();
                    showTrackingPanel(orderData.id);
                };
            }
        }
        
        // Helper function to get status badge class
        function getStatusBadgeClass(status) {
            const statusMap = {
                'Pending': 'bg-secondary',
                'Processing': 'bg-info',
                'Preparing': 'bg-primary',
                'Ready': 'bg-warning',
                'On the way': 'bg-info',
                'Delivered': 'bg-success',
                'Cancelled': 'bg-danger'
            };
            return statusMap[status] || 'bg-secondary';
        }
        
        console.log('=== DEBUG SCRIPT END ===');
    </script>
    
    <!-- Make order rows clickable -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add click handler to all order rows
        const orderRows = document.querySelectorAll('.order-row');
        
        orderRows.forEach(row => {
            row.style.cursor = 'pointer';
            
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on buttons or links
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a') || e.target.closest('button')) {
                    return;
                }
                
                const orderId = this.getAttribute('data-order-id');
                if (orderId) {
                    // Open order details in a new page
                    window.location.href = `order-details.php?order_id=${orderId}`;
                }
            });
            
            // Add hover effect
            row.addEventListener('mouseenter', function() {
                this.classList.add('table-active');
            });
            
            row.addEventListener('mouseleave', function() {
                this.classList.remove('table-active');
            });
        });
    });
    </script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add click handler for order rows
        document.querySelectorAll('.order-row').forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on action buttons
                if (e.target.closest('.btn') || e.target.closest('a')) {
                    return;
                }
                
                const orderId = this.getAttribute('data-order-id');
                if (orderId && window.loadOrderSummary) {
                    window.loadOrderSummary(orderId);
                    
                    // Scroll to order summary panel
                    const panel = document.getElementById('orderSummaryPanel');
                    if (panel) {
                        panel.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });
        
        // Add hover effect for order rows
        document.querySelectorAll('.order-row').forEach(row => {
            row.style.cursor = 'pointer';
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
    </script>
    
    <!-- GSAP Core -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/ScrollTrigger.min.js"></script>
    
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    
    <!-- Custom JavaScript -->
    <script src="js/tracking.js"></script>
    
    <!-- Animation Scripts -->
    <script src="js/dashboard-animations.js"></script>
    </script>
    

    </script>
    
    <script>
        // Global variable to store the modal instance
        let orderSummaryModal = null;

        // Test function to manually show the modal
        function testModal() {
            console.log('Test modal function called');
            const modalElement = document.getElementById('orderSummaryModal');
            if (!modalElement) {
                console.error('Modal element not found');
                return;
            }
            
            console.log('Modal element found, initializing...');
            const modal = new bootstrap.Modal(modalElement);
            console.log('Modal instance created, showing...');
            modal.show();
            console.log('Modal show called');
        }

        // Initialize when DOM is fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM fully loaded');
            
            // Add test button
            const header = document.querySelector('.card-header.bg-white');
            if (header) {
                const testBtn = document.createElement('button');
                testBtn.className = 'btn btn-warning btn-sm ms-3';
                testBtn.textContent = 'Test Modal';
                testBtn.onclick = testModal;
                header.appendChild(testBtn);
            }
            
            console.log('Initializing order summary modal...');
            const modalElement = document.getElementById('orderSummaryModal');
            if (modalElement) {
                console.log('Found order summary modal element in DOM');
                try {
                    orderSummaryModal = new bootstrap.Modal(modalElement, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                    console.log('Order summary modal initialized successfully', orderSummaryModal);
                    
                    // Test if we can show the modal
                    console.log('Testing modal show...');
                    orderSummaryModal.show();
                    setTimeout(() => {
                        console.log('Hiding test modal...');
                        orderSummaryModal.hide();
                    }, 2000);
                    
                } catch (error) {
                    console.error('Error initializing modal:', error);
                }
            } else {
                console.error('Order summary modal element not found in DOM');
                console.log('Available elements with modal class:', document.querySelectorAll('.modal'));
            }
            
            // Function to update the order summary modal with order data
            window.updateOrderSummaryModal = function(orderData) {
                console.log('Updating order summary modal with data:', orderData);
                
                try {
                    // Update order header
                    document.getElementById('modal-order-id').textContent = orderData.id || 'N/A';
                    document.getElementById('modal-order-date').textContent = orderData.date || 'N/A';
                    
                    // Update order items
                    const itemsList = document.getElementById('modal-order-items-list');
                    if (itemsList && orderData.items && Array.isArray(orderData.items)) {
                        itemsList.innerHTML = orderData.items.map(item => `
                            <tr>
                                <td>${item.name || 'Unknown Item'}</td>
                                <td class="text-end">${item.quantity || 0}</td>
                                <td class="text-end">Rs. ${item.price || 0}</td>
                                <td class="text-end">Rs. ${item.total || 0}</td>
                            </tr>
                        `).join('');
                    }
                    
                    // Update order summary
                    if (document.getElementById('modal-subtotal')) {
                        document.getElementById('modal-subtotal').textContent = `Rs. ${orderData.subtotal || 0}`;
                    }
                    if (document.getElementById('modal-order-total')) {
                        document.getElementById('modal-order-total').textContent = `Rs. ${orderData.total || 0}`;
                    }
                    
                    console.log('Order summary modal updated successfully');
                    return true;
                } catch (error) {
                    console.error('Error updating order summary modal:', error);
                    return false;
                }
            };
            
            const orderStatusElement = document.getElementById('order-status');
                if (orderStatusElement) {
                    orderStatusElement.textContent = orderData.status;
                    // Add status-specific class
                    orderStatusElement.className = `badge bg-${getStatusBadgeClass(orderData.status)}`;
                }
                
                // Update order items
                const orderItemsList = document.getElementById('order-items-list');
                if (orderItemsList) {
                    if (orderData.items && orderData.items.length > 0) {
                        orderItemsList.innerHTML = orderData.items.map(item => `
                            <tr>
                                <td>${item.name}</td>
                                <td class="text-center">${item.quantity}</td>
                                <td class="text-end">Rs. ${item.price.toFixed(2)}</td>
                                <td class="text-end">Rs. ${(item.quantity * item.price).toFixed(2)}</td>
                            </tr>
                        `).join('');
                    } else {
                        orderItemsList.innerHTML = `
                            <tr>
                                <td colspan="4" class="text-center py-4">No items in this order</td>
                            </tr>`;
                    }
                }
                
                // Update order summary
                const subtotalElement = document.getElementById('order-subtotal');
                if (subtotalElement) {
                    subtotalElement.textContent = `Rs. ${orderData.subtotal.toFixed(2)}`;
                }
                
                const deliveryFeeElement = document.getElementById('order-delivery-fee');
                if (deliveryFeeElement) {
                    deliveryFeeElement.textContent = `Rs. ${orderData.deliveryFee.toFixed(2)}`;
                }
                
                const totalElement = document.getElementById('order-total');
                if (totalElement) {
                    totalElement.textContent = `Rs. ${orderData.total.toFixed(2)}`;
                }
                
                // Update delivery address
                const deliveryAddressElement = document.getElementById('delivery-address');
                if (deliveryAddressElement) {
                    deliveryAddressElement.textContent = orderData.deliveryAddress || 'Not specified';
                }
                
                // Update payment information
                const paymentMethodElement = document.getElementById('payment-method');
                if (paymentMethodElement) {
                    paymentMethodElement.textContent = orderData.paymentMethod || 'Not specified';
                }
                
                const paymentStatusElement = document.getElementById('payment-status');
                if (paymentStatusElement) {
                    paymentStatusElement.textContent = orderData.paymentStatus || 'Pending';
                    paymentStatusElement.className = `badge ${orderData.paymentStatus === 'Paid' ? 'bg-success' : 'bg-warning'}`;
                }
            }
            
            // Helper function to get the appropriate badge class for order status
            function getStatusBadgeClass(status) {
                const statusClasses = {
                    'Pending': 'warning',
                    'Processing': 'info',
                    'Preparing': 'primary',
                    'Ready for Pickup': 'info',
                    'Out for Delivery': 'info',
                    'Delivered': 'success',
                    'Cancelled': 'danger'
                };
                return statusClasses[status] || 'secondary';
            }
            
            // Initialize other components
            if (typeof initTrackingModal === 'function') {
                initTrackingModal();
            }
            
            // Handle order action clicks (view, cancel, etc.)
            function handleOrderActionClick(e) {
                // Handle view order button
                if (e.target.closest('.view-order')) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('View order button clicked');
                    
                    const button = e.target.closest('.view-order');
                    const orderId = button.getAttribute('data-order-id');
                    console.log('Order ID:', orderId);
                    
                    // Show the order summary using the global modal instance
                    if (orderSummaryModal) {
                        console.log('Showing order summary modal');
                        
                        // Set loading state
                        const orderItemsList = document.getElementById('order-items-list');
                        if (orderItemsList) {
                            orderItemsList.innerHTML = `
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 mb-0">Loading order details...</p>
                                    </td>
                                </tr>`;
                        }
                        
                        // Set order ID
                        const orderIdElement = document.getElementById('order-id');
                        if (orderIdElement) {
                            orderIdElement.textContent = orderId;
                        }
                        
                        // Show the modal
                        orderSummaryModal.show();
                        
                        // Simulate loading order details (replace with actual API call)
                        setTimeout(() => {
                            // Mock data - replace with actual data from your backend
                            const orderData = {
                                id: orderId,
                                status: 'Preparing',
                                date: new Date().toLocaleDateString(),
                                items: [
                                    { name: 'Chicken Biryani', quantity: 2, price: 350 },
                                    { name: 'Butter Naan', quantity: 4, price: 40 },
                                    { name: 'Chicken Tikka Masala', quantity: 1, price: 450 }
                                ],
                                subtotal: 1080,
                                deliveryFee: 200,
                                total: 1280,
                                deliveryAddress: '123 Foodie Street, Cuisine City, 12345',
                                paymentMethod: 'Credit Card',
                                paymentStatus: 'Paid'
                            };
                            
                            // Update the modal with order data
                            updateOrderSummaryModal(orderData);
                        }, 500);
                    } else {
                        console.error('Order summary modal not initialized');
                    }
                    
                    return false;
                }
                
                // Handle cancel order button
                if (e.target.closest('.cancel-order')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const button = e.target.closest('.cancel-order');
                    const orderId = button.getAttribute('data-order-id');
                    const row = button.closest('tr');
                    if (confirm('Are you sure you want to cancel this order?')) {
                        cancelOrder(orderId, row);
                    }
                    return false;
                }
            }
            
            // Add new click handlers
            document.addEventListener('click', handleOrderActionClick);
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            if (typeof initOrderActions === 'function') {
                initOrderActions();
            }
            
            if (typeof ensureActiveOrdersVisible === 'function') {
                ensureActiveOrdersVisible();
            }
            
            // Check if Bootstrap is loaded
            console.log('Bootstrap Modal:', typeof bootstrap !== 'undefined' ? bootstrap.Modal : 'Bootstrap not found');
        });
        
        // Show order summary in modal
        function showOrderSummary(orderId) {
            if (!orderSummaryModal) {
                console.error('Order summary modal not initialized');
                return;
            }

            // Show loading state
            const itemsList = document.getElementById('order-items-list');
            if (itemsList) {
                itemsList.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 mb-0">Loading order details...</p>
                        </td>
                    </tr>`;
            }
            
            // Set order ID
            const orderIdElement = document.getElementById('order-id');
            if (orderIdElement) {
                orderIdElement.textContent = orderId;
            }
            
            // Show the modal
            orderSummaryModal.show();
            
            // Simulate API call to get order details
            // In a real app, this would be an AJAX call to your backend
            setTimeout(() => {
                // Mock order data - replace with actual API call
                const orderData = {
                    id: orderId,
                    date: new Date().toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }),
                    items: [
                        { name: 'Chicken Biryani', quantity: 2, price: 350 },
                        { name: 'Butter Naan', quantity: 4, price: 40 },
                        { name: 'Chicken Tikka Masala', quantity: 1, price: 450 }
                    ],
                    subtotal: 1080,
                    deliveryFee: 200,
                    total: 1280
                };
                
                // Update the modal with order data
                const orderDateElement = document.getElementById('order-date');
                if (orderDateElement) {
                    orderDateElement.textContent = orderData.date;
                }
                
                // Populate order items
                if (itemsList) {
                    itemsList.innerHTML = orderData.items.map(item => `
                        <tr>
                            <td>${item.name}</td>
                            <td class="text-end">${item.quantity}</td>
                            <td class="text-end">Rs. ${item.price.toFixed(2)}</td>
                            <td class="text-end">Rs. ${(item.quantity * item.price).toFixed(2)}</td>
                        </tr>
                    `).join('');
                }
                
                // Update summary
                const subtotalElement = document.getElementById('subtotal');
                const totalElement = document.getElementById('order-total');
                
                if (subtotalElement) {
                    subtotalElement.textContent = `Rs. ${orderData.subtotal.toFixed(2)}`;
                }
                
                if (totalElement) {
                    totalElement.textContent = `Rs. ${orderData.total.toFixed(2)}`;
                }
                
            }, 800); // Simulate network delay
        }
        
        // Initialize order actions
        function initOrderActions() {
            // Remove any existing click handlers to prevent duplicates
            document.removeEventListener('click', handleOrderActionClick);
            document.removeEventListener('click', handleTrackOrderClick);
            
            // Add new click handlers
            document.addEventListener('click', handleOrderActionClick);
            document.addEventListener('click', handleTrackOrderClick);
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        
        // Handle track order button in the summary modal
        function handleTrackOrderClick(e) {
            if (e.target.closest('#track-this-order')) {
                e.preventDefault();
                e.stopPropagation();
                
                // Hide the order summary modal
                if (orderSummaryModal) {
                    orderSummaryModal.hide();
                }
                
                // Show the tracking modal
                const orderId = document.getElementById('order-id')?.textContent;
                if (orderId) {
                    showTrackingModal(orderId);
                }
                
                return false;
            }
        }
        
        // Show order summary modal
        function showOrderSummary(orderId) {
            const modalElement = document.getElementById('orderSummaryModal');
            if (!modalElement) return;
            
            // Initialize modal if not already done
            let modal = bootstrap.Modal.getInstance(modalElement);
            if (!modal) {
                modal = new bootstrap.Modal(modalElement);
            }
            
            // Update order ID and date
            document.getElementById('order-id').textContent = orderId;
            document.getElementById('order-date').textContent = 'Placed on: ' + new Date().toLocaleDateString();
            
            // Show loading state
            const itemsList = document.getElementById('order-items-list');
            itemsList.innerHTML = `
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>`;
            
            // Show the modal
            modal.show();
            
            // Simulate API call to get order details
            getOrderDetails(orderId).then(order => {
                updateOrderSummaryUI(order);
            }).catch(error => {
                console.error('Error loading order details:', error);
                itemsList.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading order details. Please try again.
                    </div>`;
            });
        }
        
        // Simulate getting order details from API
        function getOrderDetails(orderId) {
            return new Promise((resolve) => {
                // Simulate API delay
                setTimeout(() => {
                    // Mock data - in a real app, this would come from your backend
                    const menuItems = [
                        { id: 1, name: 'Chicken Biryani', price: 350 },
                        { id: 2, name: 'Beef Burger', price: 450 },
                        { id: 3, name: 'Chicken Tikka', price: 550 },
                        { id: 4, name: 'Chicken Karahi', price: 650 },
                        { id: 5, name: 'Chicken Korma', price: 500 },
                        { id: 6, name: 'Chicken Handi', price: 700 },
                        { id: 7, name: 'Chicken Karahi', price: 650 },
                        { id: 8, name: 'Chicken Korma', price: 500 },
                        { id: 9, name: 'Chicken Handi', price: 700 }
                    ];
                    
                    // Generate random items for this order
                    const itemCount = Math.floor(Math.random() * 3) + 1; // 1-3 items
                    const items = [];
                    let subtotal = 0;
                    
                    for (let i = 0; i < itemCount; i++) {
                        const randomItem = menuItems[Math.floor(Math.random() * menuItems.length)];
                        const quantity = Math.floor(Math.random() * 2) + 1; // 1-2 quantity
                        const total = randomItem.price * quantity;
                        
                        items.push({
                            id: randomItem.id,
                            name: randomItem.name,
                            price: randomItem.price,
                            quantity: quantity,
                            total: total
                        });
                        
                        subtotal += total;
                    }
                    
                    resolve({
                        id: orderId,
                        items: items,
                        subtotal: subtotal,
                        deliveryFee: 200,
                        total: subtotal + 200,
                        status: ['Processing', 'Preparing', 'Out for Delivery', 'Delivered'][Math.floor(Math.random() * 4)],
                        date: new Date().toISOString()
                    });
                }, 500);
            });
        }
        
        // Update order summary UI with order details
        function updateOrderSummaryUI(order) {
            const itemsList = document.getElementById('order-items-list');
            
            // Generate items HTML
            const itemsHtml = order.items.map(item => `
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <h6 class="mb-1">${item.name}</h6>
                        <small class="text-muted">Quantity: ${item.quantity}</small>
                    </div>
                    <div class="text-end">
                        <div>Rs. ${item.total.toLocaleString()}</div>
                        <small class="text-muted">Rs. ${item.price.toLocaleString()} × ${item.quantity}</small>
                    </div>
                </div>
            `).join('');
            
            // Update the DOM
            itemsList.innerHTML = itemsHtml;
            document.getElementById('subtotal').textContent = `Rs. ${order.subtotal.toLocaleString()}`;
            document.getElementById('order-total').textContent = `Rs. ${order.total.toLocaleString()}`;
            
            // Set the order ID for the track button
            document.getElementById('track-this-order').setAttribute('data-order-id', order.id);
        }
        
        // Initialize tracking modal
        function initTrackingModal() {
            // Add event delegation for track buttons
            document.addEventListener('click', function(e) {
                if (e.target.closest('.track-order')) {
                    e.preventDefault();
                    const button = e.target.closest('.track-order');
                    const orderId = button.getAttribute('data-order-id');
                    showTrackingModal(orderId);
                }
                
                // Handle refresh button
                if (e.target.closest('#refresh-tracking')) {
                    e.preventDefault();
                    const button = e.target.closest('#refresh-tracking');
                    const orderId = button.getAttribute('data-order-id');
                    if (orderId) {
                        updateTrackingStatus(orderId);
                    }
                }
            });
        }
        
        // Show tracking modal
                const orderId = button.getAttribute('data-order-id');
                showTrackingModal(orderId);
            }
        });

        // Refresh tracking button
        document.getElementById('refresh-tracking')?.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            if (orderId) {
                updateTrackingStatus(orderId);
            }
        });

        // Function to show tracking modal
        function showTrackingModal(orderId) {
            const modal = document.getElementById('trackingModal');
            if (!modal) {
                console.error('Tracking modal not found');
                return;
            }
            
            // Update modal content
            const label = modal.querySelector('#trackingModalLabel');
            const refreshBtn = modal.querySelector('#refresh-tracking');
            
            if (label) label.textContent = `Order #${orderId} Tracking`;
            if (refreshBtn) refreshBtn.setAttribute('data-order-id', orderId);
            
            // Show loading state
            const statusMessage = modal.querySelector('#status-message');
            if (statusMessage) {
                statusMessage.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Loading order status...';
            }
            
            // Show the modal
            trackingModal.show();
            
            // Update with actual status
            updateTrackingStatus(orderId);
        }

        // Function to update tracking status
        async function updateTrackingStatus(orderId) {
            const refreshBtn = document.querySelector('#refresh-tracking');
            const originalBtnText = refreshBtn?.innerHTML;
            
            try {
                // Show loading state
                if (refreshBtn) {
                    refreshBtn.disabled = true;
                    refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Refreshing...';
                }
                
                // Simulate API call to get order status
                const orderStatus = await getOrderStatus(orderId);
                updateTrackingUI(orderStatus);
                
                // Get user's location for more accurate ETA
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            updateDeliveryETA(position.coords, orderStatus);
                        },
                        (error) => {
                            console.error('Error getting location:', error);
                            updateDeliveryETA(null, orderStatus);
                        }
                    );
                } else {
                    updateDeliveryETA(null, orderStatus);
                }
                
            } catch (error) {
                console.error('Error updating tracking:', error);
                showAlert('Error updating tracking information. Please try again.', 'danger');
            } finally {
                // Reset refresh button
                if (refreshBtn) {
                    refreshBtn.disabled = false;
                    refreshBtn.innerHTML = originalBtnText;
                }
            }
        }
        
        // Run when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Initialize other components
            ensureActiveOrdersVisible();
            handleOrderActions();
            
            // Ensure visibility after a short delay
            setTimeout(ensureActiveOrdersVisible, 100);
            
            // Disable GSAP animations if they exist
            if (typeof gsap !== 'undefined') {
                gsap.globalTimeline.clear();
                gsap.globalTimeline.kill();
            }    gsap.globalTimeline.pause();
            }
            
            function showTrackingPanelLegacy(orderId) {
                // This function is kept for reference but won't be used
                // The new modal-based tracking is handled by the code above
                                <div class="step-content">
                                    <h6>Order Being Prepared</h6>
                                    <p>Estimated time: ${times.orderReady} minutes</p>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: 25%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-icon"><i class="fas fa-box"></i></div>
                                <div class="step-content">
                                    <h6>Packing Order</h6>
                                    <p>Estimated time: ${times.packing} minutes</p>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-icon"><i class="fas fa-motorcycle"></i></div>
                                <div class="step-content">
                                    <h6>On The Way</h6>
                                    <p>Estimated time: ${times.onTheWay} minutes</p>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-icon"><i class="fas fa-home"></i></div>
                                <div class="step-content">
                                    <h6>Delivery</h6>
                                    <p>Estimated time: ${times.delivery} minutes</p>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tracking-summary mt-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between">
                                <span><strong>Total Estimated Time:</strong></span>
                                <span class="fw-bold">${totalTime} minutes</span>
                            </div>
                        </div>
                    </div>
                `;

                // Add tracking panel to the page
                const orderRow = document.querySelector(`tr[data-order-id="${orderId}"]`);
                if (orderRow) {
                    // Remove any existing tracking panel
                    const existingPanel = document.getElementById(`tracking-panel-${orderId}`);
                    if (existingPanel) {
                        existingPanel.remove();
                    }
                    
                    // Create and insert new tracking panel
                    const trackingRow = document.createElement('tr');
                    trackingRow.id = `tracking-row-${orderId}`;
                    trackingRow.innerHTML = `<td colspan="6" class="tracking-panel-cell">${trackingPanel}</td>`;
                    orderRow.after(trackingRow);
                    
                    // Scroll to the tracking panel
                    trackingRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }

            // Make function available globally
            window.showTrackingPanel = showTrackingPanel;
            
            // Close tracking panel
            window.closeTrackingPanel = function(orderId) {
                const trackingRow = document.getElementById(`tracking-row-${orderId}`);
                if (trackingRow) {
                    trackingRow.remove();
                }
            };

            // Close tracking panel when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.tracking-panel') && !e.target.closest('.btn-track')) {
                    document.querySelectorAll('.tracking-panel-cell').forEach(panel => {
                        panel.closest('tr').remove();
                    });
                }
            });

            // Order Details Panel Functionality
        const orderDetailsPanel = document.getElementById('orderDetailsPanel');
        const orderDetailsOverlay = document.getElementById('orderDetailsOverlay');
        const orderDetailsContent = document.getElementById('orderDetailsContent');
        const closeOrderDetailsBtn = document.getElementById('closeOrderDetails');
        let currentOrderId = null;

        // Function to show order details
        function showOrderDetails(orderId) {
            // If clicking the same order, toggle the panel
            if (currentOrderId === orderId && orderDetailsPanel.classList.contains('show')) {
                closeOrderDetails();
                return;
            }
            
            currentOrderId = orderId;
            
            // Reset content with loading state
            orderDetailsContent.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading order details...</p>
                </div>
            `;
            
            // Show the panel immediately with loading state
            orderDetailsPanel.classList.add('show');
            document.body.classList.add('modal-open');
            
            // Get order data from PHP session via AJAX
            fetch(`get_order_details.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const order = data.order;
                        let itemsHtml = '';
                        
                        // Build order items HTML
                        order.items.forEach(item => {
                            itemsHtml += `
                                <div class="order-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">${item.item_name}</h6>
                                            <small class="text-muted">${item.quantity} × Rs ${parseFloat(item.price).toFixed(2)}</small>
                                        </div>
                                        <div class="text-end ms-3">
                                            <div class="fw-bold">Rs ${(item.price * item.quantity).toFixed(2)}</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        // Build order summary HTML
                        const orderDate = new Date(order.order_date);
                        const options = { 
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        };
                        
                        const orderHtml = `
                            <div class="order-details-container">
                                <div class="order-header">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="mb-0">Order #${order.id}</h5>
                                        <span class="status-badge ${getStatusBadgeClass(order.status)}">${order.status}</span>
                                    </div>
                                    <p class="text-muted mb-3">
                                        <i class="far fa-clock me-1"></i> ${orderDate.toLocaleDateString('en-US', options)}
                                    </p>
                                </div>
                                
                                <div class="order-items">
                                    <h6 class="section-title">
                                        <i class="fas fa-utensils me-2"></i>Order Items
                                        <span class="badge bg-light text-dark ms-2">${order.items.length} items</span>
                                    </h6>
                                    <div class="items-list">
                                        ${itemsHtml}
                                    </div>
                                </div>
                                
                                <div class="order-summary">
                                    <h6 class="section-title">
                                        <i class="fas fa-receipt me-2"></i>Order Summary
                                    </h6>
                                    <div class="summary-item">
                                        <span>Subtotal</span>
                                        <span>Rs ${parseFloat(order.total_amount).toFixed(2)}</span>
                                    </div>
                                    <div class="summary-item">
                                        <span>Delivery Fee</span>
                                        <span>Rs 0.00</span>
                                    </div>
                                    <div class="summary-total">
                                        <span>Total</span>
                                        <span>Rs ${parseFloat(order.total_amount).toFixed(2)}</span>
                                    </div>
                                </div>
                                
                                <div class="delivery-address">
                                    <h6 class="section-title">
                                        <i class="fas fa-map-marker-alt me-2"></i>Delivery Address
                                    </h6>
                                    <p>${order.address || 'No address provided'}</p>
                                </div>
                                
                                <div class="order-actions mt-4">
                                    <button class="btn btn-primary w-100" onclick="window.location.href='order-details.php?id=${order.id}'">
                                        <i class="fas fa-file-invoice me-2"></i>View Full Details
                                    </button>
                                </div>
                            </div>
                        `;
                        
                        orderDetailsContent.innerHTML = orderHtml;
                    } else {
                        orderDetailsContent.innerHTML = `
                            <div class="alert alert-danger m-0">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                ${data.message || 'Failed to load order details.'}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading order details:', error);
                    orderDetailsContent.innerHTML = `
                        <div class="alert alert-danger m-0">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            An error occurred while loading order details. Please try again.
                        </div>
                    `;
                });
        }
        
        // Helper function to get status badge class
        function getStatusBadgeClass(status) {
            const statusClasses = {
                'Processing': 'bg-warning',
                'Preparing': 'bg-info',
                'On the way': 'bg-primary',
                'Delivered': 'bg-success',
                'Cancelled': 'bg-danger',
                'Completed': 'bg-success',
                'Pending': 'bg-secondary'
            };
            return statusClasses[status] || 'bg-secondary';
        }
        
        // Close order details panel
        function closeOrderDetails() {
            orderDetailsPanel.classList.remove('show');
            document.body.classList.remove('modal-open');
            // Reset scroll position
            orderDetailsContent.scrollTop = 0;
        }
        
        // Close button event
        closeOrderDetailsBtn.addEventListener('click', closeOrderDetails);
        
        // Close when clicking outside the panel or on overlay
        orderDetailsOverlay.addEventListener('click', closeOrderDetails);
        
        // Close with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && orderDetailsPanel.classList.contains('show')) {
                closeOrderDetails();
            }
        });
        
        // Make function available globally
        window.showOrderDetails = showOrderDetails;
        });
    </script>
    
    <style>
        /* Table Styles */
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }
        
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            background-color: #f8f9fa;
            color: #495057;
        }
        
        .table tbody + tbody {
            border-top: 2px solid #dee2e6;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.075);
        }
        
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Dark theme table styles */
        .dark-theme .table {
            color: #f8f9fa;
            background-color: #2d2d2d;
        }
        
        .dark-theme .table th,
        .dark-theme .table td {
            border-top-color: #444;
        }
        
        .dark-theme .table thead th {
            background-color: #343a40;
            border-bottom-color: #444;
            color: #f8f9fa;
        }
        
        .dark-theme .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.075);
        }
        
        /* Ensure Active Orders heading is visible */
        .card-header h4 {
            color: #212529 !important; /* Dark color for light theme */
            font-weight: 600 !important;
            margin: 0 !important;
            padding: 0.75rem 1.25rem !important;
        }
        
        .dark-theme .card-header h4 {
            color: #f8f9fa !important; /* Light color for dark theme */
        }
        
        .card-header {
            background-color: #f8f9fa !important; /* Light background for light theme */
            border-bottom: 1px solid #e9ecef !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }
        
        .dark-theme .card-header {
            background-color: #2d2d2d !important; /* Dark background for dark theme */
            border-bottom-color: #444 !important;
        }
        
        /* Responsive Tables */
        @media (max-width: 767.98px) {
            .table-responsive {
                border: 0;
            }
            .table thead {
                display: none;
            }
            .table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
            }
            .table tbody tr td {
                display: flex;
                justify-content: space-between;
                padding: 0.75rem;
                border-top: 1px solid #dee2e6;
            }
            .table tbody tr td:before {
                content: attr(data-label);
                font-weight: 600;
                margin-right: 1rem;
                flex: 0 0 120px;
            }
            .table tbody tr td:last-child {
                border-bottom: 0;
            }
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
        
        /* Tracking Panel Styles */
        .tracking-panel {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            padding: 1rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1000;
            max-width: 100%;
        }
        
        .tracking-steps {
            position: relative;
            padding-left: 2.5rem;
        }
        
        .step {
            position: relative;
            padding: 1rem 0 1rem 3rem;
            border-left: 2px solid #e9ecef;
        }
        
        .step:last-child {
            border-left: 2px solid transparent;
        }
        
        .step-icon {
            position: absolute;
            left: -1.25rem;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            z-index: 2;
        }
        
        .step.active .step-icon {
            background: #0d6efd;
            color: white;
        }
        
        .step h6 {
            margin-bottom: 0.25rem;
            font-weight: 600;
        }
        
        .step p {
            margin-bottom: 0.5rem;
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        .progress {
            height: 6px;
            background-color: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .tracking-summary {
            font-size: 0.9375rem;
        }
        
        .btn-track {
            white-space: nowrap;
        }
        
        /* Order Details Panel */
        .order-details-panel {
            position: fixed;
            top: 0;
            right: -100%;
            width: 100%;
            max-width: 400px;
            height: 100%;
            background: white;
            z-index: 1050;
            transition: right 0.3s ease;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .order-details-panel.show {
            right: 0;
        }
        
        .order-details-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1040;
        }
        
        .order-details-panel.show + .order-details-overlay {
            opacity: 1;
            visibility: visible;
        }
        
        .order-details-content {
            padding: 1.25rem;
            height: 100%;
            overflow-y: auto;
        }
        
        .order-details-panel.show .order-details-content {
            transform: translateX(0);
        }
        
        .order-details-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
        }
        
        .order-details-header h4 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #6c757d;
            padding: 0.25rem 0.5rem;
            cursor: pointer;
            transition: color 0.2s;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }
        
        .close-btn:hover {
            color: #333;
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .order-details-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }
        
        .order-details-container {
            max-width: 100%;
        }
        
        .order-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            font-weight: 600;
            border-radius: 50rem;
            text-transform: capitalize;
        }
        
        .section-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin: 1.5rem 0 1rem;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }
        
        .order-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.25rem;
            margin: 1.5rem 0;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            color: #555;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            font-size: 1.1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }
        
        .delivery-address {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.25rem;
            margin: 1.5rem 0;
        }
        
        .delivery-address p {
            margin: 0.5rem 0 0;
            line-height: 1.5;
        }
        
        .order-actions {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .order-details-panel {
                max-width: 100%;
            }
            
            .order-details-body {
                padding: 1.25rem;
            }
            
            .order-details-header {
                padding: 1rem 1.25rem;
            }
            
            .order-header h5 {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 0.8rem;
            }
        }
        
        /* Animation for modal open/close */
        @keyframes slideInRight {
            from { right: -100%; }
            to { right: 0; }
        }
        
        @keyframes slideOutRight {
            from { right: 0; }
            to { right: -100%; }
        }
        
        /* Prevent body scroll when modal is open */
        body.modal-open {
            overflow: hidden;
            padding-right: 0 !important;
        }
        
        @media (max-width: 768px) {
            .tracking-steps {
                padding-left: 1.5rem;
            }
            
            .step {
                padding-left: 2.5rem;
            }
        }
    </style>
    

    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
