<?php
require_once 'config.php';

// Get leave balance for an employee
function getLeaveBalance($employeeId) {
    $conn = getDBConnection();
    $sql = "SELECT * FROM leave_balance WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $balance = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $balance;
}

// Apply for leave
function applyLeave($employeeId, $leaveType, $startDate, $endDate, $reason) {
    $conn = getDBConnection();
    
    // First check leave balance
    $balance = getLeaveBalance($employeeId);
    $leaveTypeBalance = $leaveType . '_leave';
    
    if ($balance[$leaveTypeBalance] <= 0) {
        return ['status' => 'error', 'message' => 'Insufficient leave balance'];
    }
    
    // Calculate number of days
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $days = $end->diff($start)->days + 1;
    
    if ($days > $balance[$leaveTypeBalance]) {
        return ['status' => 'error', 'message' => 'Requested days exceed available balance'];
    }
    
    // Insert leave application
    $sql = "INSERT INTO leave_log (employee_id, leave_type, start_date, end_date, reason) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $employeeId, $leaveType, $startDate, $endDate, $reason);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return ['status' => 'success', 'message' => 'Leave application submitted successfully'];
    } else {
        $stmt->close();
        $conn->close();
        return ['status' => 'error', 'message' => 'Error submitting leave application'];
    }
}

// Get leave history for an employee
function getLeaveHistory($employeeId) {
    $conn = getDBConnection();
    $sql = "SELECT * FROM leave_log WHERE employee_id = ? ORDER BY date_applied DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
    return $history;
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'apply':
            if (isset($data['employee_id'], $data['leave_type'], $data['start_date'], $data['end_date'], $data['reason'])) {
                $result = applyLeave(
                    $data['employee_id'],
                    $data['leave_type'],
                    $data['start_date'],
                    $data['end_date'],
                    $data['reason']
                );
                sendResponse($result['status'], $result['message']);
            } else {
                sendResponse('error', 'Missing required fields');
            }
            break;
            
        default:
            sendResponse('error', 'Invalid action');
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $employeeId = $_GET['employee_id'] ?? null;
    
    if (!$employeeId) {
        sendResponse('error', 'Employee ID is required');
    }
    
    switch ($action) {
        case 'balance':
            $balance = getLeaveBalance($employeeId);
            sendResponse('success', 'Leave balance retrieved', $balance);
            break;
            
        case 'history':
            $history = getLeaveHistory($employeeId);
            sendResponse('success', 'Leave history retrieved', $history);
            break;
            
        default:
            sendResponse('error', 'Invalid action');
    }
}
?>
