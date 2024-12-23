<?php
require_once 'config.php';
require_once 'session.php';

// User login
function login($username, $password) {
    $conn = getDBConnection();
    
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
    
    if (!$user) {
        return ['status' => 'error', 'message' => 'Invalid username'];
    }
    
    if (!password_verify($password, $user['password'])) {
        return ['status' => 'error', 'message' => 'Invalid password'];
    }
    
    // Create a clean user object without sensitive data
    $userResponse = [
        'employee_id' => $user['employee_id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'department' => $user['department'],
        'role' => $user['role'],
        'username' => $user['username']
    ];
    
    // Set the session
    setUserSession($userResponse);
    
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
                    session_write_close(); // Ensure session is written
                }
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => $result['status'],
                    'message' => $result['status'] === 'success' ? 'Login successful' : $result['message'],
                    'data' => $result['status'] === 'success' ? $result['user'] : null
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Username and password are required']);
            }
            break;
            
        case 'logout':
            clearUserSession();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
            break;
            
        default:
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
    exit;
}
?>
