<?php
/**
 * OCR Log Checker Script
 * This script helps you check OCR logs from PHP error log
 */

$errorLogPath = 'C:\xampp\php\logs\php_error_log';
$linesToShow = 100; // Number of recent lines to show

// Check if log file exists
if (!file_exists($errorLogPath)) {
    echo "Error log file not found at: {$errorLogPath}\n";
    echo "This is normal if no errors have been logged yet.\n";
    echo "The log file will be created automatically when the first log entry is written.\n\n";
    echo "To test OCR logging:\n";
    echo "1. Upload an image file through the web interface\n";
    echo "2. Check this script again after the upload\n\n";
    exit(0);
}

// Read the last N lines from the log file
$lines = file($errorLogPath);
$totalLines = count($lines);
$startLine = max(0, $totalLines - $linesToShow);

echo "=== OCR Log Checker ===\n";
echo "Log file: {$errorLogPath}\n";
echo "Total lines in log: {$totalLines}\n";
echo "Showing last {$linesToShow} lines:\n";
echo str_repeat("=", 80) . "\n\n";

// Filter and display OCR-related logs
$ocrLogs = [];
$allRecentLogs = array_slice($lines, $startLine);

foreach ($allRecentLogs as $line) {
    if (stripos($line, 'OCR') !== false || 
        stripos($line, 'Tesseract') !== false || 
        stripos($line, 'Google Vision') !== false ||
        stripos($line, 'image') !== false && stripos($line, 'extracted') !== false) {
        $ocrLogs[] = trim($line);
    }
}

if (empty($ocrLogs)) {
    echo "No OCR-related logs found in the last {$linesToShow} lines.\n";
    echo "\nShowing all recent logs:\n";
    echo str_repeat("-", 80) . "\n";
    foreach ($allRecentLogs as $line) {
        echo trim($line) . "\n";
    }
} else {
    echo "Found " . count($ocrLogs) . " OCR-related log entries:\n";
    echo str_repeat("-", 80) . "\n";
    foreach ($ocrLogs as $log) {
        echo $log . "\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "To view logs in real-time, use: Get-Content {$errorLogPath} -Wait -Tail 50\n";
echo "Or use: tail -f {$errorLogPath} (if you have tail command)\n";

