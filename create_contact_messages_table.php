<?php
require_once 'includes/db_connection.php';

header('Content-Type: text/plain');
echo "Creating contact_messages table...\n";

try {
    // SQL to create contact_messages table
    $sql = "CREATE TABLE IF NOT EXISTS `contact_messages` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL,
        `phone` VARCHAR(20) DEFAULT NULL,
        `subject` VARCHAR(255) NOT NULL,
        `message` TEXT NOT NULL,
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `user_agent` VARCHAR(255) DEFAULT NULL,
        `is_read` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `email` (`email`),
        KEY `is_read` (`is_read`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo "✅ contact_messages table created successfully\n";
        
        // Check if columns exist and add any missing ones
        $alter_queries = [
            "ALTER TABLE `contact_messages` MODIFY `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            "ALTER TABLE `contact_messages` MODIFY `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            "ALTER TABLE `contact_messages` ADD COLUMN IF NOT EXISTS `response` TEXT DEFAULT NULL AFTER `message`",
            "ALTER TABLE `contact_messages` ADD COLUMN IF NOT EXISTS `response_by` INT(11) DEFAULT NULL AFTER `response`",
            "ALTER TABLE `contact_messages` ADD COLUMN IF NOT EXISTS `response_at` TIMESTAMP NULL DEFAULT NULL AFTER `response_by`"
        ];
        
        foreach ($alter_queries as $query) {
            if ($conn->query($query) === TRUE) {
                echo "✅ " . substr($query, 0, 60) . "...\n";
            } else {
                echo "❌ Error: " . $conn->error . "\n";
            }
        }
        
    } else {
        echo "❌ Error creating table: " . $conn->error . "\n";
    }
    
    // Close connection
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\nProcess completed.\n";
?>
