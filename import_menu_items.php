<?php
require_once 'includes/db_connection.php';

header('Content-Type: text/plain');
echo "Starting menu items import...\n";

// Function to parse menu items from HTML file
function parseMenuItemsFromHTML($filePath) {
    $html = file_get_contents($filePath);
    $menuItems = [];
    
    // Use regex to extract menu items
    $pattern = '/<div class="category-item" data-item="([^"]+)">\s*<div>\s*<strong>([^<]+)<\/strong><br>\s*<small class="text-muted">([^<]+)<\/small>\s*<\/div>\s*<button[^>]+data-item="[^"]*"[^>]+data-name="([^"]*)"[^>]+data-price="(\d+)"/s';
    
    if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $price = (int)$match[5] / 100; // Convert from cents to rupees
            $description = trim(str_replace('•', '', $match[3])); // Clean up description
            
            // Extract category from description if possible, otherwise default to "Main Course"
            $category = 'Main Course';
            if (stripos($description, 'pasta') !== false) {
                $category = 'Pasta';
            } elseif (stripos($description, 'chicken') !== false) {
                $category = 'Chicken';
            } elseif (stripos($description, 'beef') !== false) {
                $category = 'Beef';
            } elseif (stripos($description, 'fish') !== false || stripos($description, 'salmon') !== false) {
                $category = 'Seafood';
            } elseif (stripos($description, 'vegetable') !== false || stripos($description, 'risotto') !== false) {
                $category = 'Vegetarian';
            }
            
            $menuItems[] = [
                'name' => trim($match[2]),
                'description' => $description,
                'price' => $price,
                'category' => $category,
                'is_vegetarian' => (int)($category === 'Vegetarian' || $category === 'Pasta'),
                'is_vegan' => (int)($category === 'Vegetarian'),
                'is_gluten_free' => 0, // Default to not gluten free
                'is_spicy' => (int)(stripos($description, 'buffalo') !== false || stripos($description, 'spicy') !== false),
                'prep_time' => rand(15, 30), // Random prep time between 15-30 minutes
                'calories' => rand(400, 1200), // Random calories between 400-1200
                'display_order' => count($menuItems) + 1
            ];
        }
    }
    
    return $menuItems;
}

try {
    // Parse menu items from HTML file
    $menuItems = parseMenuItemsFromHTML('order-panel.html');
    
    if (empty($menuItems)) {
        throw new Exception("No menu items found in the HTML file.");
    }
    
    echo "Found " . count($menuItems) . " menu items to import.\n";
    
    // First, ensure the menu_categories table exists
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
        UNIQUE KEY `name` (`name`),
        KEY `display_order` (`display_order`),
        KEY `is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ menu_categories table created/verified\n";
    } else {
        throw new Exception("Error creating menu_categories table: " . $conn->error);
    }
    
    // Create menu_items table if it doesn't exist
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
        echo "✅ menu_items table created/verified\n";
    } else {
        throw new Exception("Error creating menu_items table: " . $conn->error);
    }
    
    // Get all unique categories from the menu items
    $categories = [];
    foreach ($menuItems as $item) {
        $categories[$item['category']] = [
            'name' => $item['category'],
            'description' => $item['category'] . ' dishes',
            'display_order' => count($categories) + 1
        ];
    }
    
    // Insert/update categories and get their IDs
    $categoryIds = [];
    $stmt = $conn->prepare("
        INSERT INTO `menu_categories` (`name`, `description`, `display_order`, `is_active`)
        VALUES (?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE 
            `description` = VALUES(`description`),
            `display_order` = VALUES(`display_order`),
            `is_active` = 1,
            `updated_at` = CURRENT_TIMESTAMP
    ");
    
    $selectStmt = $conn->prepare("SELECT `id` FROM `menu_categories` WHERE `name` = ?");
    
    foreach ($categories as $category) {
        $stmt->bind_param("ssi", $category['name'], $category['description'], $category['display_order']);
        if ($stmt->execute()) {
            // Get the category ID
            $selectStmt->bind_param("s", $category['name']);
            $selectStmt->execute();
            $result = $selectStmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $categoryIds[$category['name']] = $row['id'];
            }
        } else {
            echo "⚠️ Warning: Could not insert/update category '{$category['name']}': " . $stmt->error . "\n";
        }
    }
    
    echo "✅ Processed " . count($categories) . " categories\n";
    
    // Insert menu items
    $inserted = 0;
    $updated = 0;
    $errors = 0;
    
    $stmt = $conn->prepare("
        INSERT INTO `menu_items` 
        (`name`, `description`, `price`, `category_id`, `is_vegetarian`, `is_vegan`, `is_gluten_free`, `is_spicy`, `prep_time`, `calories`, `display_order`, `is_available`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE 
            `description` = VALUES(`description`),
            `price` = VALUES(`price`),
            `category_id` = VALUES(`category_id`),
            `is_vegetarian` = VALUES(`is_vegetarian`),
            `is_vegan` = VALUES(`is_vegan`),
            `is_gluten_free` = VALUES(`is_gluten_free`),
            `is_spicy` = VALUES(`is_spicy`),
            `prep_time` = VALUES(`prep_time`),
            `calories` = VALUES(`calories`),
            `display_order` = VALUES(`display_order`),
            `is_available` = 1,
            `updated_at` = CURRENT_TIMESTAMP
    ");
    
    foreach ($menuItems as $item) {
        $categoryId = $categoryIds[$item['category']] ?? null;
        
        if (!$categoryId) {
            echo "⚠️ Warning: Category '{$item['category']}' not found for item '{$item['name']}'. Skipping.\n";
            $errors++;
            continue;
        }
        
        $stmt->bind_param(
            "ssdiiiiiiii",
            $item['name'],
            $item['description'],
            $item['price'],
            $categoryId,
            $item['is_vegetarian'],
            $item['is_vegan'],
            $item['is_gluten_free'],
            $item['is_spicy'],
            $item['prep_time'],
            $item['calories'],
            $item['display_order']
        );
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                if ($stmt->insert_id) {
                    $inserted++;
                } else {
                    $updated++;
                }
            }
        } else {
            echo "⚠️ Error inserting/updating item '{$item['name']}': " . $stmt->error . "\n";
            $errors++;
        }
    }
    
    echo "✅ Import completed!\n";
    echo "   - Inserted: $inserted new items\n";
    echo "   - Updated: $updated existing items\n";
    if ($errors > 0) {
        echo "   - Errors: $errors items had issues\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo "\nProcess completed.\n";
?>
