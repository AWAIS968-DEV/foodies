<?php
// check_table_structure.php
require_once __DIR__ . '/config/database.php';

$result = $conn->query("DESCRIBE order_status_history");
echo "<pre>order_status_history table structure:\n";
while ($row = $result->fetch_assoc()) {
    echo "{$row['Field']} - {$row['Type']}\n";
}