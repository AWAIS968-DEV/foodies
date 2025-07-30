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

echo "<h2>Setting up database tables...</h2>";

// SQL to create tables
$sql_orders = "
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `delivery_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$sql_order_items = "
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `menu_item_id` (`menu_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$sql_menu_items = "
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Execute table creation
$tables = [
    'orders' => $sql_orders,
    'order_items' => $sql_order_items,
    'menu_items' => $sql_menu_items
];

foreach ($tables as $table => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "<p>Table '$table' created successfully or already exists.</p>";
    } else {
        echo "<p>Error creating table '$table': " . $conn->error . "</p>";
    }
}

// First, clear any existing data
echo "<h3>Clearing any existing data...</h3>";
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE `order_items`");
$conn->query("TRUNCATE TABLE `orders`");
$conn->query("TRUNCATE TABLE `menu_items`");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Add sample data
$sample_data = [
    'menu_items' => [
        "INSERT INTO `menu_items` (`id`, `name`, `price`) VALUES
        (1, 'Zinger Burger', 350.00),
        (2, 'Chicken Fries', 200.00),
        (3, 'Pepsi 500ml', 80.00)"
    ],
    'orders' => [
        "INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `order_date`) VALUES
        (123, 1, 1010.00, 'confirmed', NOW())"
    ],
    'order_items' => [
        "INSERT INTO `order_items` (`order_item_id`, `order_id`, `menu_item_id`, `quantity`, `price`) VALUES
        (1, 123, 1, 1, 350.00),  -- Zinger Burger
        (2, 123, 2, 2, 400.00),  -- Chicken Fries (2 x 200)
        (3, 123, 3, 3, 240.00)   -- Pepsi 500ml (3 x 80)"
    ]
];

// Execute each insert statement separately
echo "<h3>Adding sample data...</h3>";
foreach ($sample_data as $table => $queries) {
    echo "<p>Processing data for $table...</p>";
    foreach ($queries as $sql) {
        echo "<p>Executing: " . htmlspecialchars(substr($sql, 0, 100)) . "...</p>";
        if ($conn->query($sql) === TRUE) {
            echo "<p>✓ Data added successfully to $table.</p>";
        } else {
            echo "<p>Error adding data to $table: " . $conn->error . "</p>";
            echo "<p>SQL: " . htmlspecialchars($sql) . "</p>";
        }
    }
}

// Verify data was inserted
$tables_to_check = ['menu_items', 'orders', 'order_items'];
foreach ($tables_to_check as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
    $row = $result->fetch_assoc();
    echo "<p>Table '$table' now contains " . $row['count'] . " rows.</p>";
}

echo "<h3>Setup complete! <a href='order-detail.php?order_id=1'>View Sample Order</a></h3>";

$conn->close();
?>
