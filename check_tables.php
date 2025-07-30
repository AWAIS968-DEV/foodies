<?php
require_once 'db_connection.php';

// Function to display table structure
function displayTableStructure($conn, $tableName) {
    echo "<h3>Structure of $tableName table:</h3>";
    $result = $conn->query("DESCRIBE `$tableName`");
    
    if ($result) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-bottom: 20px;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . (is_null($row['Default']) ? 'NULL' : htmlspecialchars($row['Default'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Table $tableName doesn't exist or there was an error: " . $conn->error . "</p>";
    }
}

// Function to display table data
function displayTableData($conn, $tableName) {
    echo "<h3>Data in $tableName table:</h3>";
    $result = $conn->query("SELECT * FROM `$tableName`");
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin-bottom: 30px;'>";
        
        // Display headers
        echo "<tr>";
        $fields = $result->fetch_fields();
        foreach ($fields as $field) {
            echo "<th>" . htmlspecialchars($field->name) . "</th>";
        }
        echo "</tr>";
        
        // Reset pointer
        $result->data_seek(0);
        
        // Display data
        while ($row = $result->fetch_row()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . ($value === null ? 'NULL' : htmlspecialchars($value)) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data found in $tableName or table doesn't exist.</p>";
    }
}

// Check and display both tables
$tables = ['orders', 'active_orders'];

foreach ($tables as $table) {
    // Check if table exists
    $tableExists = $conn->query("SHOW TABLES LIKE '$table'");
    
    if ($tableExists && $tableExists->num_rows > 0) {
        displayTableStructure($conn, $table);
        displayTableData($conn, $table);
    } else {
        echo "<p>Table '$table' does not exist in the database.</p>";
    }
}

$conn->close();
?>
