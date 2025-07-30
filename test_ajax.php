<?php
// Test script to check AJAX functionality
session_start();
require_once 'config/database.php';

// Set JSON content type
header('Content-Type: application/json');

// Simple response to test if the file is accessible
echo json_encode([
    'success' => true,
    'message' => 'AJAX test successful',
    'session_id' => session_id(),
    'user_id' => $_SESSION['user_id'] ?? null
]);
