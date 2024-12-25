<?php
require_once __DIR__ . '/config.php';

try {
    // Try to connect to MySQL server
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "Connected to MySQL server successfully\n";
    
    // Check if database exists
    $result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($result->num_rows == 0) {
        throw new Exception("Database '" . DB_NAME . "' does not exist");
    }
    echo "Database '" . DB_NAME . "' exists\n";
    
    // Select the database
    if (!$conn->select_db(DB_NAME)) {
        throw new Exception("Could not select database: " . $conn->error);
    }
    
    // Check required tables
    $required_tables = ['employee_details', 'user_credentials', 'leave_balance', 'leave_log'];
    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows == 0) {
            throw new Exception("Table '$table' does not exist");
        }
        echo "Table '$table' exists\n";
        
        // Show table structure
        $result = $conn->query("DESCRIBE $table");
        echo "\nStructure of '$table':\n";
        while ($row = $result->fetch_assoc()) {
            echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
