<?php
require_once __DIR__ . '/config.php';

try {
    // Get database connection
    $conn = getDBConnection();
    
    // Enable error reporting for debugging
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    $query = "
        SELECT 
            e.employee_id,
            e.name,
            e.email,
            e.department,
            DATE_FORMAT(e.date_joined, '%Y-%m-%d') as date_joined,
            e.status,
            u.role
        FROM employee_details e
        LEFT JOIN user_credentials u ON e.employee_id = u.employee_id
        ORDER BY e.employee_id DESC
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        // Convert null values to empty strings or default values
        $row['department'] = $row['department'] ?? '-';
        $row['role'] = $row['role'] ?? 'employee';
        $row['status'] = $row['status'] ?? 'active';
        $employees[] = $row;
    }
    
    // Log the response for debugging
    error_log("Get employees response: " . json_encode(['employees' => $employees]));
    
    // Send the response with employees array under the data key
    header('Content-Type: application/json');
    echo json_encode([
        'status' => true,
        'message' => 'Employees retrieved successfully',
        'data' => ['employees' => $employees]
    ]);

} catch (Exception $e) {
    error_log("Error in get_employees.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => false,
        'message' => 'Error retrieving employees: ' . $e->getMessage(),
        'data' => null
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
