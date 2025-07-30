<?php
require_once 'includes/db_connection.php';

header('Content-Type: text/plain');
echo "Creating menu tables...\n";

try {
    // Create menu_categories table
    $sql = "CREATE TABLE IF NOT EXISTS `menu_categories` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(50) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `image` VARCHAR(255) DEFAULT NULL,
        `display_order` INT(11) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `display_order` (`display_order`),
        KEY `is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if ($conn->query($sql) === TRUE) {
        echo "✅ menu_categories table created successfully\n";
    } else {
        throw new Exception("Error creating menu_categories table: " . $conn->error);
    }

    // Create menu_items table
    $sql = "CREATE TABLE IF NOT EXISTS `menu_items` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `price` DECIMAL(10,2) NOT NULL,
        `category_id` INT(11) DEFAULT NULL,
        `image` VARCHAR(255) DEFAULT NULL,
        `is_vegetarian` TINYINT(1) DEFAULT 0,
        `is_vegan` TINYINT(1) DEFAULT 0,
        `is_gluten_free` TINYINT(1) DEFAULT 0,
        `is_spicy` TINYINT(1) DEFAULT 0,
        `is_available` TINYINT(1) DEFAULT 1,
        `prep_time` INT(11) DEFAULT NULL COMMENT 'Preparation time in minutes',
        `calories` INT(11) DEFAULT NULL,
        `display_order` INT(11) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `category_id` (`category_id`),
        KEY `is_available` (`is_available`),
        KEY `display_order` (`display_order`),
        FULLTEXT KEY `name_description` (`name`, `description`),
        CONSTRAINT `fk_menu_items_category` 
            FOREIGN KEY (`category_id`) 
            REFERENCES `menu_categories` (`id`) 
            ON DELETE SET NULL 
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql) === TRUE) {
        echo "✅ menu_items table created successfully\n";
    } else {
        throw new Exception("Error creating menu_items table: " . $conn->error);
    }

    // Insert default categories if they don't exist
    $categories = [
        [1, 'Appetizers', 'Delicious starters to begin your meal', 1],
        [2, 'Main Course', 'Hearty and satisfying main dishes', 2],
        [3, 'Desserts', 'Sweet treats to end your meal', 3],
        [4, 'Beverages', 'Refreshing drinks and beverages', 4],
        [5, 'Sides', 'Perfect accompaniments to your meal', 5]
    ];

    $stmt = $conn->prepare("
        INSERT IGNORE INTO `menu_categories` 
        (`id`, `name`, `description`, `display_order`) 
        VALUES (?, ?, ?, ?)
    ");

    $count = 0;
    foreach ($categories as $category) {
        $stmt->bind_param('issi', $category[0], $category[1], $category[2], $category[3]);
        if ($stmt->execute()) {
            $count++;
        }
    }
    
    echo "✅ Inserted/updated $count default categories\n";
    
    // Close connection
    $conn->close();
    
    echo "\n✅ Menu tables setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nProcess completed.\n";
?>
