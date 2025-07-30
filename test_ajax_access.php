<?php
// Test script to check AJAX endpoint accessibility
header('Content-Type: text/plain');

$test_url = 'http://' . $_SERVER['HTTP_HOST'] . '/foodies/ajax/cancel_order.php';
$test_file = __DIR__ . '/ajax/cancel_order.php';

// Check if file exists
if (!file_exists($test_file)) {
    die("Error: File not found at: $test_file");
}

echo "File exists at: $test_file\n";
echo "Trying to access: $test_url\n\n";

// Try to access the file via HTTP
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => 'order_id=999',
        'ignore_errors' => true
    ]
]);

$response = @file_get_contents($test_url, false, $context);

if ($response === false) {
    $error = error_get_last();
    die("Failed to access URL. Error: " . $error['message']);
}

// Get the HTTP response code
preg_match('|HTTP\/\d+\.\d+\s+(\d+)|', $http_response_header[0], $matches);
$status_code = $matches[1];

echo "HTTP Status: $status_code\n";
if ($status_code == 200) {
    echo "Success! The AJAX endpoint is accessible.\n";
    echo "Response: " . $response;
} else {
    echo "The server returned status code: $status_code\n";
    echo "Response headers:\n";
    print_r($http_response_header);
    echo "\nResponse body:\n$response";
}
