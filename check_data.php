<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "foodies_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Checking Database Tables and Data</h2>";

// Function to display table data
function displayTableData($conn, $table) {
    echo "<h3>Data in table: $table</h3>";
    
    $result = $conn->query("SHOW COLUMNS FROM $table");
    if (!$result) {
        echo "<p>Error getting columns: " . $conn->error . "</p>";
        return;
    }
    
    // Get column names
    $columns = [];
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Get data
    $data = $conn->query("SELECT * FROM $table");
    if (!$data) {
        echo "<p>Error getting data: " . $conn->error . "</p>";
        return;
    }
    
    if ($data->num_rows > 0) {
        echo "<table border='1'><tr>";
        // Output headers
        foreach ($columns as $column) {
            echo "<th>$column</th>";
        }
        echo "</tr>";
        
        // Output data
        while($row = $data->fetch_assoc()) {
            echo "<tr>";
            foreach ($columns as $column) {
                echo "<td>" . htmlspecialchars($row[$column] ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data found in table: $table</p>";
    }
}

// Check each table
displayTableData($conn, 'orders');
displayTableData($conn, 'order_items');
displayTableData($conn, 'menu_items');

// Check if order with ID 1 exists
$result = $conn->query("SELECT * FROM orders WHERE id = 1");
if ($result->num_rows === 0) {
    echo "<h3>Error: No order with ID 1 found in the database.</h3>";
    echo "<p>Please run setup_tables.php first to create sample data.</p>";
} else {
    $order = $result->fetch_assoc();
    echo "<h3>Order #1 Details:</h3>";
    echo "<pre>" . print_r($order, true) . "</pre>";
    
    // Check order items
    $items = $conn->query("SELECT * FROM order_items WHERE order_id = 1");
    if ($items->num_rows > 0) {
        echo "<h4>Order Items:</h4>";
        while($item = $items->fetch_assoc()) {
            echo "<pre>" . print_r($item, true) . "</pre>";
        }
    } else {
        echo "<p>No items found for order #1</p>";
    }
}

$conn->close();
?>
