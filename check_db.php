<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'config/database.php';

// Check if tables exist
$tables = ['users', 'orders', 'order_items'];
$results = [];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    $results[$table] = $result->num_rows > 0;
    
    if ($results[$table]) {
        // Get table structure
        $structure = $conn->query("DESCRIBE $table");
        $results["{$table}_columns"] = [];
        while ($row = $structure->fetch_assoc()) {
            $results["{$table}_columns"][] = $row;
        }
    }
}

// Check if there are any orders
$order_count = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$results['order_count'] = $order_count;

// Output results
echo "<h2>Database Diagnostic Results</h2>";
echo "<pre>";
print_r($results);
echo "</pre>";

// If there are orders, show a sample
if ($order_count > 0) {
    $sample_order = $conn->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 1")->fetch_assoc();
    echo "<h3>Sample Order</h3>";
    echo "<pre>";
    print_r($sample_order);
    echo "</pre>";
}

// Close connection
$conn->close();
?>
