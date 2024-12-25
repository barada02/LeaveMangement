<?php
require_once __DIR__ . '/config.php';

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['admin_id'])) {
        throw new Exception('Admin ID is required');
    }

    $admin_id = $data['admin_id'];
    $status_filter = isset($data['status']) ? $data['status'] : 'pending';

    // Get database connection
    $conn = getDBConnection();
    
    // Build the query based on status filter
    $query = "
        SELECT 
            l.leave_log_id,
            l.employee_id,
            e.name as employee_name,
            l.leave_type,
            DATE_FORMAT(l.start_date, '%Y-%m-%d') as from_date,
            DATE_FORMAT(l.end_date, '%Y-%m-%d') as to_date,
            DATEDIFF(l.end_date, l.start_date) + 1 as days,
            l.reason,
            l.status,
            l.date_applied,
            COALESCE(l.note, '') as note
        FROM leave_log l
        JOIN employee_details e ON l.employee_id = e.employee_id
        WHERE 1=1
    ";

    // Add status filter if not 'all'
    if ($status_filter !== 'all') {
        $query .= " AND l.status = '" . strtolower($status_filter) . "'";
    }

    $query .= " ORDER BY l.date_applied DESC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        // Format leave type for display
        $row['leave_type'] = ucfirst($row['leave_type']) . ' Leave';
        
        // Format status for display
        $row['status'] = ucfirst($row['status']);
        
        $requests[] = $row;
    }
    
    // Log the response for debugging
    error_log("Get leave requests response: " . json_encode(['requests' => $requests]));
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => true,
        'message' => 'Leave requests retrieved successfully',
        'data' => ['requests' => $requests]
    ]);

} catch (Exception $e) {
    error_log("Error in get_leave_requests.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => false,
        'message' => 'Error retrieving leave requests: ' . $e->getMessage(),
        'data' => null
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
