<?php
require_once 'includes/db_connection.php';

header('Content-Type: text/plain');

try {
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "Table 'users' exists.\n";
        
        // Get table structure
        $structure = $conn->query("DESCRIBE users");
        echo "Structure of 'users' table:\n";
        while ($row = $structure->fetch_assoc()) {
            echo "- {$row['Field']} ({$row['Type']})\n";
        }
    } else {
        echo "Table 'users' does NOT exist.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
