<?php
require_once 'db_connection.php';

header('Content-Type: text/plain');

try {
    // Check if tables exist
    $tables = ['users', 'orders', 'order_items', 'active_orders'];
    $table_structure = [];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "Table '$table' exists.\n";
            // Get table structure
            $structure = $conn->query("DESCRIBE $table");
            echo "Structure of '$table':\n";
            while ($row = $structure->fetch_assoc()) {
                echo "- {$row['Field']} ({$row['Type']})\n";
            }
            echo "\n";
        } else {
            echo "Table '$table' does NOT exist.\n\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
