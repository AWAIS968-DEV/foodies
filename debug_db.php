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

echo "<h2>Database Connection Successful</h2>";

// Check if orders table exists
$orders_table = $conn->query("SHOW TABLES LIKE 'orders'");

if ($orders_table->num_rows > 0) {
    echo "<h3>Orders Table Structure:</h3>";
    
    // Get table structure
    $result = $conn->query("DESCRIBE orders");
    if ($result->num_rows > 0) {
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while($row = $result->fetch_assoc()) {
            $highlight = $row['Field'] == 'id' ? ' style="background-color: #ffeb3b;"' : '';
            echo "<tr$highlight>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show sample data (first 5 rows)
        echo "<h3>Sample Order Data (first 5 rows):</h3>";
        $data = $conn->query("SELECT id, order_number, customer_name, status, order_date, total_amount FROM orders ORDER BY id DESC LIMIT 5");
        if ($data->num_rows > 0) {
            echo "<table border='1'><tr>";
            // Print headers
            $fields = $data->fetch_fields();
            foreach ($fields as $field) {
                echo "<th>" . $field->name . "</th>";
            }
            echo "</tr>";
            
            // Print data
            $data->data_seek(0);
            while($row = $data->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No order data found.</p>";
        }
        
        // Check for large order IDs
        echo "<h3>Checking for Large Order IDs:</h3>";
        $large_id = "1753635878453"; // The problematic order ID
        $result = $conn->query("SELECT id, order_number, status FROM orders WHERE id = $large_id");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "<div style='background-color: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<p>Found order with large ID:</p>";
            echo "<pre>" . print_r($row, true) . "</pre>";
            echo "</div>";
        } else {
            echo "<div style='background-color: #f8d7da; padding: 10px; border-radius: 5px;'>";
            echo "<p>Order with ID $large_id not found.</p>";
            
            // Check max ID in database
            $max_id = $conn->query("SELECT MAX(id) as max_id FROM orders")->fetch_assoc()['max_id'];
            echo "<p>Maximum order ID in database: $max_id</p>";
            
            // Check if we can insert a large ID (for testing)
            if (isset($_GET['test_large_id'])) {
                $test_id = $_GET['test_large_id'];
                $test_number = 'TEST' . time();
                $sql = "INSERT INTO orders (id, order_number, customer_name, customer_email, customer_phone, delivery_address, order_date, status, payment_method, payment_status, subtotal, total_amount) 
                        VALUES (?, ?, 'Test Customer', 'test@example.com', '1234567890', 'Test Address', NOW(), 'pending', 'cash_on_delivery', 'pending', 10.00, 10.00)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $test_id, $test_number);
                
                if ($stmt->execute()) {
                    echo "<p style='color: green;'>Successfully inserted test order with ID: $test_id</p>";
                    // Delete test order
                    $conn->query("DELETE FROM orders WHERE id = $test_id");
                } else {
                    echo "<p style='color: red;'>Failed to insert test order: " . $conn->error . "</p>";
                }
            } else {
                echo "<p><a href='?test_large_id=1753635878453' class='btn btn-primary'>Test Inserting Large Order ID (1753635878453)</a></p>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<p>Could not retrieve table structure for 'orders'.</p>";
    }
} else {
    echo "<div style='color: red;'><h3>Error: 'orders' table does not exist in the database.</h3></div>";
}

// Close connection
$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .btn { display: inline-block; padding: 8px 16px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 5px 0; }
    .btn:hover { background-color: #0056b3; };
                $fields = $data->fetch_fields();
                echo "<table border='1'><tr>";
                foreach ($fields as $field) {
                    echo "<th>" . $field->name . "</th>";
                }
                echo "</tr>";
                
                while($row = $data->fetch_row()) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No data found in $table_name</p>";
            }
        }
        echo "<hr>";
    }
} else {
    echo "No tables found in the database.";
}

$conn->close();
?>
