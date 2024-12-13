<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['name']) || !isset($data['email']) || !isset($data['department']) || !isset($data['dateJoined'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields. Please fill all the required information.'
    ]);
    exit;
}

try {
    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM employee_details WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists. Please use a different email address.'
        ]);
        exit;
    }

    // Insert into employee_details
    $stmt = $conn->prepare("INSERT INTO employee_details (name, email, department, date_joined, status) VALUES (?, ?, ?, ?, 'active')");
    $stmt->bind_param("ssss", $data['name'], $data['email'], $data['department'], $data['dateJoined']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Employee registered successfully!'
        ]);
    } else {
        throw new Exception("Error inserting employee data");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred. Please try again later.'
    ]);
}

$conn->close();
?>
