<?php
// Display the last 20 lines of the PHP error log
header('Content-Type: text/plain');

// Try to get the error log path
$errorLog = ini_get('error_log');
if (empty($errorLog)) {
    $errorLog = 'error_log'; // Default name if not set
}

// If the error log exists and is readable, show the last 20 lines
if (file_exists($errorLog) && is_readable($errorLog)) {
    echo "=== Last 20 lines of error log ($errorLog) ===\n\n";
    $output = [];
    exec("tail -n 20 " . escapeshellarg($errorLog), $output);
    echo implode("\n", $output);
} else {
    echo "Error log not found or not readable at: " . htmlspecialchars($errorLog) . "\n";
    echo "Trying alternative methods...\n\n";
    
    // Try to find error log in common locations
    $possibleLogs = [
        '/var/log/apache2/error.log',
        '/var/log/httpd/error_log',
        '/var/log/php_errors.log',
        'C:/xampp/php/logs/php_error_log',
        'C:/xampp/apache/logs/error.log',
        'C:/wamp/logs/php_error.log',
        __DIR__ . '/php_errors.log'
    ];
    
    $found = false;
    foreach ($possibleLogs as $log) {
        if (file_exists($log) && is_readable($log)) {
            echo "=== Found log file: $log ===\n\n";
            $output = [];
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows doesn't have 'tail', so we'll use a different approach
                $lines = file($log);
                $output = array_slice($lines, -20);
            } else {
                exec("tail -n 20 " . escapeshellarg($log), $output);
            }
            echo implode("\n", $output);
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "Could not find any error logs. Here are some things to check:\n";
        echo "1. Check your php.ini file for the 'error_log' directive\n";
        echo "2. Make sure display_errors is On in your php.ini\n";
        echo "3. Check your web server's error log\n\n";
        
        // Show current error reporting settings
        echo "Current PHP error reporting: " . ini_get('error_reporting') . "\n";
        echo "Display errors: " . ini_get('display_errors') . "\n";
        echo "Log errors: " . ini_get('log_errors') . "\n";
        echo "Error log: " . ini_get('error_log') . "\n";
    }
}

// Also show any recent errors from the current session
echo "\n\n=== Recent errors from current session ===\n
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to get the last error
$lastError = error_get_last();
if ($lastError) {
    print_r($lastError);
} else {
    echo "No recent errors in current session.\n";
}

// Show PHP info about error handling
echo "\n\n=== PHP Error Handling Configuration ===\n";
$settings = [
    'error_reporting',
    'display_errors',
    'log_errors',
    'error_log',
    'track_errors',
    'html_errors',
    'xmlrpc_errors'
];

foreach ($settings as $setting) {
    echo "$setting: " . ini_get($setting) . "\n";
}
