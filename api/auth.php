<?php
require_once 'config.php';

// User login
function login($username, $password) {
    $conn = getDBConnection();
    
    // Debug: Log the username
    error_log("Login attempt for username: " . $username);
    
    // First get the user credentials
    $sql = "SELECT uc.*, ed.name, ed.email, ed.department 
            FROM user_credentials uc 
            JOIN employee_details ed ON uc.employee_id = ed.employee_id 
            WHERE uc.username = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Debug: Log detailed user data
    error_log("User data found: " . ($user ? "Yes" : "No"));
    if ($user) {
        error_log("User ID: " . $user['user_id']);
        error_log("Employee ID: " . $user['employee_id']);
        error_log("Name: " . $user['name']);
        error_log("Department: " . $user['department']);
    }
    
    if (!$user) {
        error_log("No user found with username: " . $username);
        return ['status' => 'error', 'message' => 'Invalid username'];
    }
    
    if (!password_verify($password, $user['password'])) {
        error_log("Invalid password for username: " . $username);
        return ['status' => 'error', 'message' => 'Invalid password'];
    }
    
    // Debug: Log successful login
    error_log("Login successful for user: " . $user['name']);
    
    // Create a clean user object without sensitive data
    $userResponse = [
        'employee_id' => $user['employee_id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'department' => $user['department'],
        'role' => $user['role'],
        'username' => $user['username'] // Adding username for frontend use
    ];
    
    // Double check the employee details
    $checkSql = "SELECT * FROM employee_details WHERE employee_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $user['employee_id']);
    $checkStmt->execute();
    $employeeResult = $checkStmt->get_result();
    $employeeData = $employeeResult->fetch_assoc();
    
    if ($employeeData) {
        error_log("Employee verification - Name matches: " . ($employeeData['name'] === $user['name'] ? "Yes" : "No"));
        error_log("Database employee name: " . $employeeData['name']);
        error_log("Joined user name: " . $user['name']);
    }
    
    return ['status' => 'success', 'user' => $userResponse];
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'login':
            if (isset($data['username'], $data['password'])) {
                $result = login($data['username'], $data['password']);
                if ($result['status'] === 'success') {
                    error_log("Sending response data: " . print_r($result['user'], true));
                }
                sendResponse($result['status'], 
                           $result['status'] === 'success' ? 'Login successful' : $result['message'],
                           $result['status'] === 'success' ? $result['user'] : null);
            } else {
                sendResponse('error', 'Username and password are required');
            }
            break;
            
        default:
            sendResponse('error', 'Invalid action');
    }
}
?>
