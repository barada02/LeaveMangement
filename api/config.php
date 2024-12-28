<?php
// Enable error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'leavems');

// Default leave values
define('DEFAULT_SICK_LEAVE', 12);
define('DEFAULT_CASUAL_LEAVE', 12);
define('DEFAULT_EARNED_LEAVE', 0);
define('DEFAULT_FESTIVAL_LEAVE', 2);
define('DEFAULT_TOTAL_LEAVE', 41); // Sum of all leave types

// Create database connection
function getDBConnection() {
    try {
        error_log("Attempting to connect to database...");
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        error_log("Database connection successful");
        return $conn;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        sendResponse(false, "Database connection error: " . $e->getMessage());
        exit;
    }
}

// Function to handle API response
function sendResponse($status, $message, $data = null) {
    try {
        if (headers_sent($filename, $linenum)) {
            error_log("Headers already sent in $filename on line $linenum");
        }
        
        if (!headers_sent()) {
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
        }
        
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
        
        if (ob_get_length()) ob_clean();
        
        error_log("Sending response: " . json_encode($response));
        echo json_encode($response);
        exit;
    } catch (Exception $e) {
        error_log("Error in sendResponse: " . $e->getMessage());
        // Last resort error response
        echo json_encode([
            'status' => false,
            'message' => 'Internal server error',
            'error' => $e->getMessage()
        ]);
        exit;
    }
}
?>
