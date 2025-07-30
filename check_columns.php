<?php
require_once 'config/db.php';

// Check if the status column exists in the orders table
$query = "SHOW COLUMNS FROM `orders` LIKE 'status'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "The 'status' column exists in the orders table.\n";
    $column = $result->fetch_assoc();
    echo "Column details: \n";
    print_r($column);
} else {
    echo "The 'status' column does NOT exist in the orders table.\n";
    
    // Show all columns in the orders table
    echo "\nAll columns in orders table:\n";
    $allColumns = $conn->query("SHOW COLUMNS FROM `orders`");
    while ($col = $allColumns->fetch_assoc()) {
        echo $col['Field'] . ' (' . $col['Type'] . ')' . "\n";
    }
}

$conn->close();
?>
