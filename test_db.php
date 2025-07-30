<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<h2>Testing Database Connection</h2>';

// Check if db_connection.php exists
if (!file_exists('db_connection.php')) {
    die('<div style="color: red;">Error: db_connection.php not found in ' . __DIR__ . '</div>');
}

echo '<div style="color: green;">✓ db_connection.php found</div>';

// Try to include the database connection
try {
    require_once 'db_connection.php';
    echo '<div style="color: green;">✓ db_connection.php included successfully</div>';
    
    // Check if $conn exists and is a MySQLi object
    if (!isset($conn)) {
        throw new Exception('$conn variable is not set');
    }
    
    if (!($conn instanceof mysqli)) {
        throw new Exception('$conn is not a valid MySQLi connection');
    }
    
    echo '<div style="color: green;">✓ $conn is a valid MySQLi connection</div>';
    
    // Test the connection
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    echo '<div style="color: green;">✓ Successfully connected to database</div>';
    
    // Get database info
    echo '<h3>Database Info:</h3>';
    echo '<ul>';
    echo '<li>MySQL Server Version: ' . $conn->server_version . '</li>';
    echo '<li>MySQL Client Version: ' . $conn->client_version . '</li>';
    echo '<li>Database: ' . $conn->query('SELECT DATABASE()')->fetch_row()[0] . '</li>';
    echo '</ul>';
    
    // List tables
    echo '<h3>Database Tables:</h3>';
    $result = $conn->query('SHOW TABLES');
    if ($result && $result->num_rows > 0) {
        echo '<ul>';
        while ($row = $result->fetch_row()) {
            echo '<li>' . $row[0] . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<div style="color: orange;">No tables found in the database</div>';
    }
    
} catch (Exception $e) {
    echo '<div style="color: red;">Error: ' . $e->getMessage() . '</div>';
    
    // Show the contents of db_connection.php for debugging
    echo '<h3>db_connection.php contents:</h3>';
    echo '<pre>' . htmlspecialchars(file_get_contents('db_connection.php')) . '</pre>';
}
?>
