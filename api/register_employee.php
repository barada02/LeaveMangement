<?php
// Turn off error reporting for the API endpoint
error_reporting(0);
ini_set('display_errors', 0);

require_once 'config.php';
require_once 'session.php';

// Helper function to send JSON response
function sendJsonResponse($status, $message, $data = null) {
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    $response = [
        'status' => $status,
        'message' => $message,
        'data' => $data
    ];
    
    echo json_encode($response);
    exit;
}

try {
    // Get JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['department']) || !isset($data['dateJoined'])) {
        sendJsonResponse(false, 'Missing required fields. Please fill all the required information.', null);
    }

    $conn = getDBConnection();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM employee_details WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        sendJsonResponse(false, 'Email already exists. Please use a different email address.', null);
    }

    // Insert into employee_details
    $stmt = $conn->prepare("INSERT INTO employee_details (name, email, department, date_joined, status) VALUES (?, ?, ?, ?, 'active')");
    $stmt->bind_param("ssss", $data['name'], $data['email'], $data['department'], $data['dateJoined']);
    
    if ($stmt->execute()) {
        sendJsonResponse(true, 'Employee registered successfully', null);
    } else {
        sendJsonResponse(false, 'Failed to register employee: ' . $stmt->error, null);
    }

    $conn->close();
} catch (Exception $e) {
    sendJsonResponse(false, 'Error: ' . $e->getMessage(), null);
}
?>
