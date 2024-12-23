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

function getProfileData($employeeId) {
    try {
        $conn = getDBConnection();
        if (!$conn) {
            throw new Exception("Database connection failed");
        }
        
        $sql = "SELECT ed.*, uc.username, uc.role 
                FROM employee_details ed 
                JOIN user_credentials uc ON ed.employee_id = uc.employee_id 
                WHERE ed.employee_id = ?";
                
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Query preparation failed");
        }
        
        $stmt->bind_param("i", $employeeId);
        if (!$stmt->execute()) {
            throw new Exception("Query execution failed");
        }
        
        $result = $stmt->get_result();
        $profile = $result->fetch_assoc();
        
        if (!$profile) {
            return ['status' => 'error', 'message' => 'Profile not found'];
        }
        
        // Remove sensitive information
        unset($profile['password']);
        
        return ['status' => 'success', 'data' => $profile];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
}

// Start output buffering
ob_start();

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get current user from session
    $currentUser = checkSession();
    
    $result = getProfileData($currentUser['employee_id']);
    sendJsonResponse(
        $result['status'],
        $result['status'] === 'success' ? 'Profile data retrieved' : $result['message'],
        $result['status'] === 'success' ? $result['data'] : null
    );
} else {
    sendJsonResponse('error', 'Invalid request method');
}
?>
