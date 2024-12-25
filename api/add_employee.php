<?php
require_once __DIR__ . '/config.php';

error_log("Starting add_employee.php script");

try {
    // Get POST data
    $raw_data = file_get_contents('php://input');
    error_log('Received raw data: ' . $raw_data);
    
    if (empty($raw_data)) {
        throw new Exception('No data received');
    }
    
    $data = json_decode($raw_data, true);
    error_log('Decoded data: ' . print_r($data, true));
    
    if ($data === null) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    // Validate required fields
    $required_fields = ['name', 'email', 'date_joined', 'username', 'password', 'role'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            error_log("Missing required field: $field");
            throw new Exception("Field '$field' is required");
        }
    }
    error_log("All required fields present");

    // Get database connection
    error_log("Getting database connection");
    $conn = getDBConnection();
    error_log("Database connection successful");

    // Start transaction
    error_log("Starting transaction");
    if (!$conn->begin_transaction()) {
        throw new Exception('Could not start transaction: ' . $conn->error);
    }
    error_log("Transaction started");

    try {
        // Insert into employee_details
        error_log("Preparing employee_details insert");
        $stmt = $conn->prepare("
            INSERT INTO employee_details (name, email, department, date_joined, status)
            VALUES (?, ?, ?, ?, 'active')
        ");
        
        if (!$stmt) {
            throw new Exception('Prepare failed for employee_details: ' . $conn->error);
        }
        
        $department = empty($data['department']) ? null : $data['department'];
        
        error_log("Binding parameters for employee_details: " . 
                 "name={$data['name']}, " .
                 "email={$data['email']}, " .
                 "department=$department, " .
                 "date_joined={$data['date_joined']}");
                 
        if (!$stmt->bind_param("ssss", 
            $data['name'],
            $data['email'],
            $department,
            $data['date_joined']
        )) {
            throw new Exception('Parameter binding failed for employee_details: ' . $stmt->error);
        }
        
        error_log("Executing employee_details insert");
        if (!$stmt->execute()) {
            throw new Exception('Employee insert failed: ' . $stmt->error);
        }
        
        $employee_id = $conn->insert_id;
        error_log("Employee inserted with ID: $employee_id");

        // Insert into user_credentials
        error_log("Preparing user_credentials insert");
        $stmt = $conn->prepare("
            INSERT INTO user_credentials (employee_id, username, password, role, petname)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            throw new Exception('Prepare failed for user_credentials: ' . $conn->error);
        }
        
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $petname = $data['petname'] ?? '';
        
        error_log("Binding parameters for user_credentials");
        if (!$stmt->bind_param("issss", 
            $employee_id,
            $data['username'],
            $hashed_password,
            $data['role'],
            $petname
        )) {
            throw new Exception('Parameter binding failed for user_credentials: ' . $stmt->error);
        }
        
        error_log("Executing user_credentials insert");
        if (!$stmt->execute()) {
            throw new Exception('User credentials insert failed: ' . $stmt->error);
        }
        error_log("User credentials inserted");

        // Commit transaction
        error_log("Committing transaction");
        if (!$conn->commit()) {
            throw new Exception('Could not commit transaction: ' . $conn->error);
        }
        error_log("Transaction committed successfully");
        
        sendResponse(true, 'Employee added successfully', ['employee_id' => $employee_id]);

    } catch (Exception $e) {
        error_log("Error in transaction: " . $e->getMessage());
        if ($conn->ping()) {
            $conn->rollback();
            error_log("Transaction rolled back");
        }
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in add_employee.php: " . $e->getMessage());
    sendResponse(false, $e->getMessage());
} finally {
    if (isset($conn)) {
        error_log("Closing database connection");
        $conn->close();
    }
    if (ob_get_length()) ob_end_clean();
}
?>
