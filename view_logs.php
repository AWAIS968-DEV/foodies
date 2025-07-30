<?php
// Simple error log viewer
header('Content-Type: text/plain');
$logFile = 'C:\AWAIS\php\logs\php_error_log';

if (file_exists($logFile) && is_readable($logFile)) {
    echo "=== Last 20 lines of error log ===\n\n";
    $lines = file($logFile);
    $lastLines = array_slice($lines, -20);
    echo implode("", $lastLines);
} else {
    echo "Error log not found or not readable at: " . htmlspecialchars($logFile) . "\n";
    echo "Please check the path to your PHP error log in php.ini\n";
}

// Also show any recent errors from the current session
echo "\n\n=== Recent errors from current session ===\n";
$lastError = error_get_last();
if ($lastError) {
    print_r($lastError);
} else {
    echo "No recent errors in current session.\n";
}
