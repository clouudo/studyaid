<?php
require_once 'app/config/database.php';

use App\Config\Database;

echo "Connecting to database...\n";
$db = new Database();
$conn = $db->connect();

if ($conn) {
    echo "Connected.\n";
    
    // Check if column exists first
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'studyaid' AND TABLE_NAME = 'homework_helper' AND COLUMN_NAME = 'instruction'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            $sql = "ALTER TABLE homework_helper ADD COLUMN instruction TEXT DEFAULT NULL AFTER status";
            $conn->exec($sql);
            echo "Column 'instruction' added successfully.\n";
        } else {
            echo "Column 'instruction' already exists.\n";
        }
    } catch (PDOException $e) {
        echo "Error updating table: " . $e->getMessage() . "\n";
    }
} else {
    echo "Failed to connect to database.\n";
}
?>
