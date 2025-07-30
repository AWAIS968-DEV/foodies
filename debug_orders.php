<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Check if db_connection.php exists
if (!file_exists('db_connection.php')) {
    die('Error: db_connection.php not found. Please check the file exists in the correct location.');
}

// Include database connection
try {
    require_once 'db_connection.php';
    
    // Test database connection
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception('Database connection failed: $conn is not a valid MySQLi connection');
    }
    
    // Simple query to test connection
    $conn->query('SELECT 1');
    
} catch (Exception $e) {
    die('Database connection error: ' . $e->getMessage() . 
        ' in ' . $e->getFile() . ' on line ' . $e->getLine());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die('Please login first');
}

// Get all orders for the current user
$stmt = $conn->prepare("SELECT id, user_id, status, total_amount, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Get all orders from the database
$allOrdersStmt = $conn->query("SELECT id, user_id, status, total_amount, created_at FROM orders ORDER BY id DESC LIMIT 10");
$allOrders = $allOrdersStmt->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Debug Orders for User ID: <?php echo $_SESSION['user_id']; ?></h2>
        
        <h4 class="mt-4">Your Orders:</h4>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td>Rs <?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo $order['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">No orders found for this user.</div>
        <?php endif; ?>

        <h4 class="mt-5">Recent Orders (All Users):</h4>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>User ID</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Date</th>
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

        <div class="mt-4">
            <h4>Test Order Cancellation:</h4>
            <form id="cancelForm" class="row g-3">
                <div class="col-auto">
                    <label for="orderId" class="visually-hidden">Order ID</label>
                    <input type="number" class="form-control" id="orderId" placeholder="Enter Order ID">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary mb-3">Test Cancel</button>
                </div>
            </form>
            <div id="result" class="mt-3"></div>
        </div>
    </div>

    <script>
    document.getElementById('cancelForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const orderId = document.getElementById('orderId').value;
        const resultDiv = document.getElementById('result');
        
        if (!orderId) {
            resultDiv.innerHTML = '<div class="alert alert-warning">Please enter an order ID</div>';
            return;
        }
        
        resultDiv.innerHTML = '<div class="alert alert-info">Processing...</div>';
        
        fetch('cancel_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'order_id=' + encodeURIComponent(orderId)
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            let html = '<div class="alert ' + (data.success ? 'alert-success' : 'alert-danger') + '">';
            html += '<strong>' + (data.success ? 'Success!' : 'Error') + '</strong> ' + data.message;
            
            if (data.debug) {
                html += '<pre class="mt-2">' + JSON.stringify(data.debug, null, 2) + '</pre>';
            }
            
            html += '</div>';
            resultDiv.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
        });
    });
    </script>
</body>
</html>
