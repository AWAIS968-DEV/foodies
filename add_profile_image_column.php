<?php
require_once 'includes/db_connection.php';

header('Content-Type: text/plain');

try {
    // Check if profile_image column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
    
    if ($result->num_rows === 0) {
        // Add profile_image column
        $sql = "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) NULL DEFAULT NULL AFTER email";
        
        if ($conn->query($sql) === TRUE) {
            echo "Successfully added profile_image column to users table.\n";
        } else {
            echo "Error adding profile_image column: " . $conn->error . "\n";
        }
    } else {
        echo "profile_image column already exists in users table.\n";
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = __DIR__ . '/uploads/profile_images';
    if (!file_exists($upload_dir)) {
        if (mkdir($upload_dir, 0755, true)) {
            echo "Created uploads directory: $upload_dir\n";
        } else {
            echo "Failed to create uploads directory: $upload_dir\n";
        }
    } else {
        echo "Uploads directory already exists: $upload_dir\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
