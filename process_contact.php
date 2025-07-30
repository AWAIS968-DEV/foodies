<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/config/database.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => 'An error occurred. Please try again.'
];

try {
    // Check if the request is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // Get form data
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $createdAt = date('Y-m-d H:i:s');

    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        throw new Exception('All fields are required.');
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address.');
    }

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $message, $ipAddress, $userAgent, $createdAt);

    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => 'Thank you for contacting us! We\'ll get back to you soon.'
        ];
    } else {
        throw new Exception('Failed to save your message. Please try again.');
    }

    $stmt->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Close database connection
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
