<?php
require_once 'includes/db_connection.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to get status badge class
function getStatusBadgeClass($status) {
    $status = strtolower($status);
    $classes = [
        'processing' => 'bg-warning',
        'preparing' => 'bg-info',
        'on the way' => 'bg-primary',
        'delivered' => 'bg-success',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
        'pending' => 'bg-secondary'
    ];
    return $classes[$status] ?? 'bg-secondary';
}

// Function to display orders in a table
function displayOrders($orders, $emptyMessage = 'No orders found') {
    if (empty($orders)) {
        echo "<div class='alert alert-info'>$emptyMessage</div>";
        return;
    }
    
    echo '<div class="table-responsive">';
    echo '<table class="table table-hover align-middle">';
    echo '<thead class="table-light d-none d-md-table-header-group">';
    echo '<tr>';
    echo '<th>Order #</th>';
    echo '<th>Date</th>';
    echo '<th class="d-none d-md-table-cell">Customer</th>';
    echo '<th>Items</th>';
    echo '<th>Total</th>';
    echo '<th>Status</th>';
    echo '<th class="text-end">Actions</th>';
    echo '</tr>';
    echo '</thead><tbody>';
    
    foreach ($orders as $order) {
        $orderDate = new DateTime($order['order_date']);
        $statusClass = getStatusBadgeClass($order['status']);
        
        echo '<tr class="position-relative">';
        echo '<td data-label="Order #">#' . htmlspecialchars($order['id']) . '</td>';
        echo '<td data-label="Date">' . $orderDate->format('M d, Y h:i A') . '</td>';
        echo '<td data-label="Customer" class="d-none d-md-table-cell">' . htmlspecialchars($order['customer_name']) . '</td>';
        
        // Display first item + count of remaining
        $itemCount = count($order['items']);
        $firstItem = $itemCount > 0 ? $order['items'][0] : null;
        echo '<td data-label="Items">';
        if ($firstItem) {
            echo '<div class="d-flex flex-column">';
            echo '<span class="text-nowrap">' . htmlspecialchars($firstItem['item_name']) . '</span>';
            if ($firstItem['quantity'] > 1) {
                echo '<small class="text-muted">Qty: ' . $firstItem['quantity'] . '</small>';
            }
            if ($itemCount > 1) {
                echo '<small class="text-primary">+' . ($itemCount - 1) . ' more item' . ($itemCount > 2 ? 's' : '') . '</small>';
            }
            echo '</div>';
        } else {
            echo 'N/A';
        }
        echo '</td>';
        
        echo '<td data-label="Total"><strong>Rs ' . number_format($order['total_amount'], 2) . '</strong></td>';
        echo '<td data-label="Status"><span class="badge ' . $statusClass . ' d-inline-flex align-items-center">' . 
             '<i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i> ' . 
             ucfirst($order['status']) . '</span></td>';
        echo '<td data-label="Actions" class="text-md-end">';
        echo '<button onclick="showOrderDetails(\'' . $order['id'] . '\')" class="btn btn-sm btn-outline-primary w-100 w-md-auto">';
        echo '<i class="fas fa-eye d-none d-md-inline-block"></i>';
        echo '<span class="d-md-none">View</span>';
        echo '</button>';
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table></div>';
}

// Get all orders from session
$allOrders = [];
if (isset($_SESSION['orders']) && is_array($_SESSION['orders'])) {
    $allOrders = $_SESSION['orders'];
    // Sort by date descending
    usort($allOrders, function($a, $b) {
        return strtotime($b['order_date']) - strtotime($a['order_date']);
    });
}

// Filter active orders (not delivered, cancelled, or completed)
$activeStatuses = ['processing', 'preparing', 'on the way', 'pending'];
$activeOrders = array_filter($allOrders, function($order) use ($activeStatuses) {
    return in_array(strtolower($order['status']), $activeStatuses);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Foodies</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Responsive Table Styles */
        @media (max-width: 767.98px) {
            .table-responsive {
                border: 0;
                margin-bottom: 1rem;
                overflow-y: hidden;
            }
            .table thead {
                display: none;
            }
            .table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #dee2e6;
                border-radius: 0.5rem;
                padding: 1rem;
                position: relative;
            }
            .table tbody tr td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0;
                border: none;
                position: relative;
                padding-left: 50%;
                min-height: 2.5rem;
            }
            .table tbody tr td:before {
                content: attr(data-label);
                position: absolute;
                left: 0.75rem;
                width: 45%;
                padding-right: 1rem;
                font-weight: 600;
                color: #495057;
            }
            .table tbody tr td:last-child {
                justify-content: flex-end;
                padding-left: 0.75rem;
            }
            .table tbody tr td:last-child:before {
                display: none;
            }
            .badge {
                padding: 0.35em 0.65em;
                font-size: 0.75em;
            }
        }

        :root {
            --primary-color: #4a6bff;
            --primary-hover: #3a5bef;
            --text-primary: #2c3e50;
            --text-secondary: #6c757d;
            --border-color: #e9ecef;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
        }
        
        body {
            background-color: var(--bg-light);
            color: var(--text-primary);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        /* Tab Navigation */
        .nav-tabs {
            border: none;
            position: relative;
            margin: 2rem 0;
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }
        
        .nav-tabs::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }
        
        .nav-tabs .nav-item {
            margin-right: 0.5rem;
            flex-shrink: 0;
        }
        
        .nav-tabs .nav-link {
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            border-radius: 8px 8px 0 0;
            padding: 0.85rem 1.75rem;
            margin: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            background-color: transparent;
            border: 1px solid transparent;
            border-bottom: 3px solid transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 160px;
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--primary-color);
            background-color: rgba(74, 107, 255, 0.08);
            border-color: rgba(74, 107, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background-color: var(--white);
            border-color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(74, 107, 255, 0.12);
            transform: translateY(-2px);
        }
        
        .nav-tabs .nav-link .badge {
            font-size: 0.7rem;
            padding: 0.35em 0.65em;
            margin-left: 0.5rem;
            font-weight: 700;
            border-radius: 50px;
            min-width: 1.8em;
            height: 1.8em;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .nav-tabs .nav-link.active .badge {
            background-color: var(--primary-color) !important;
            color: white !important;
        }
        
        /* Active tab indicator */
        .nav-tabs::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background-color: var(--border-color);
            z-index: 0;
        }
        
        /* Tab content */
        .tab-content {
            background-color: var(--white);
            border-radius: 0 0 12px 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            position: relative;
            z-index: 1;
            margin-top: -1px;
            border: 1px solid var(--border-color);
            border-top: none;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .nav-tabs {
                margin: 1.5rem -1rem 1.5rem -1rem;
                padding: 0 1rem;
                scroll-padding: 0 1rem;
            }
            
            .nav-tabs .nav-link {
                padding: 0.65rem 1.25rem;
                font-size: 0.85rem;
                min-width: 140px;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .tab-content {
                padding: 1.5rem;
                margin: -1px 0 0 0;
                border-radius: 0 0 8px 8px;
            }
        }
        
        /* Animation for tab content */
        .tab-pane.fade.show {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Badge styles */
        .badge {
            font-weight: 600;
            padding: 0.5em 0.9em;
            font-size: 0.8rem;
            border-radius: 50px;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            max-width: 100%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .badge.bg-primary {
            background-color: var(--primary-color) !important;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container-fluid py-3 py-md-4 px-3 px-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Order Management</h1>
        </div>
        
        <ul class="nav nav-tabs" id="ordersTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">
                    Active Orders <span class="badge bg-primary ms-1"><?php echo count($activeOrders); ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                    All Orders <span class="badge bg-secondary ms-1"><?php echo count($allOrders); ?></span>
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="ordersTabContent">
            <div class="tab-pane fade show active" id="active" role="tabpanel" aria-labelledby="active-tab">
                <?php 
                displayOrders(
                    $activeOrders, 
                    '<div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No active orders found.</p>
                    </div>'
                ); 
                ?>
            </div>
            <div class="tab-pane fade" id="all" role="tabpanel" aria-labelledby="all-tab">
                <?php 
                displayOrders(
                    $allOrders, 
                    '<div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No orders found.</p>
                    </div>'
                ); 
                ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to handle order details view
        function showOrderDetails(orderId) {
            // Use the same function from dashboard
            if (typeof window.showOrderDetails === 'function') {
                window.showOrderDetails(orderId);
            } else {
                // Fallback to page navigation if function not available
                window.location.href = 'order-details.php?id=' + orderId;
            }
        }
        
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>
