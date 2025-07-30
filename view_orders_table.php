<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'db_connection.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Please login first');
}

// Get all orders for the current user
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$userOrders = [];
while ($row = $result->fetch_assoc()) {
    $userOrders[] = $row;
}

// Get all orders (for admin view)
$allOrders = [];
$allResult = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 10");
if ($allResult) {
    while ($row = $allResult->fetch_assoc()) {
        $allOrders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Orders - Foodies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Your Orders</h2>
        <?php if (empty($userOrders)): ?>
            <div class="alert alert-info">You don't have any orders yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Order ID</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userOrders as $order): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['status']); ?></td>
                                <td>Rs <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><?php echo $order['created_at']; ?></td>
                                <td>
                                    <?php if (in_array(strtolower($order['status']), ['new', 'processing'])): ?>
                                        <button class="btn btn-sm btn-danger cancel-order-btn" 
                                                data-order-id="<?php echo $order['id']; ?>">
                                            Cancel Order
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <h3 class="mt-5">Recent Orders (All Users)</h3>
        <?php if (empty($allOrders)): ?>
            <div class="alert alert-info">No orders found in the system.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Order ID</th>
                            <th>User ID</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allOrders as $order): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo $order['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['status']); ?></td>
                                <td>Rs <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><?php echo $order['created_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Add click handler for cancel buttons
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.cancel-order-btn').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                if (confirm('Are you sure you want to cancel order #' + orderId + '?')) {
                    fetch('cancel_order.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'order_id=' + encodeURIComponent(orderId)
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while processing your request.');
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
