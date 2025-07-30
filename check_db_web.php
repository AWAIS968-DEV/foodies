<?php
require_once 'includes/db_connection.php';

echo "<h1>Database Structure Check</h1>";

try {
    // Check if tables exist
    $tables = ['users', 'orders', 'order_items', 'active_orders'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<h3>Table '$table' exists.</h3>";
            // Get table structure
            $structure = $conn->query("DESCRIBE $table");
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-bottom: 20px;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            while ($row = $structure->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['Field']}</td>";
                echo "<td>{$row['Type']}</td>";
                echo "<td>{$row['Null']}</td>";
                echo "<td>{$row['Key']}</td>";
                echo "<td>".($row['Default'] ?? 'NULL')."</td>";
                echo "<td>{$row['Extra']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Table '$table' does NOT exist.</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

$conn->close();
?>
