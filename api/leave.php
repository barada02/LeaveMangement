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
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // First check leave balance
        $balance = getLeaveBalance($employeeId);
        $leaveTypeBalance = $leaveType . '_leave';
        
        if (!$balance) {
            throw new Exception('Leave balance record not found');
        }
        
        if ($balance[$leaveTypeBalance] <= 0) {
            throw new Exception('Insufficient leave balance');
        }
        
        // Calculate number of days
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $days = $end->diff($start)->days + 1;
        
        if ($days > $balance[$leaveTypeBalance]) {
            throw new Exception("Requested {$days} days exceed available balance of {$balance[$leaveTypeBalance]} days");
        }
        
        // Check for overlapping leave requests
        $sql = "SELECT COUNT(*) as count FROM leave_log 
                WHERE employee_id = ? 
                AND status != 'rejected'
                AND ((start_date BETWEEN ? AND ?) 
                OR (end_date BETWEEN ? AND ?))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $employeeId, $startDate, $endDate, $startDate, $endDate);
        $stmt->execute();
        $overlap = $stmt->get_result()->fetch_assoc();
        
        if ($overlap['count'] > 0) {
            throw new Exception('You already have a leave request for these dates');
        }
        
        // Insert leave application
        $sql = "INSERT INTO leave_log (employee_id, leave_type, start_date, end_date, reason, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $employeeId, $leaveType, $startDate, $endDate, $reason);
        
        if (!$stmt->execute()) {
            throw new Exception('Error submitting leave application');
        }
        
        // Commit transaction
        $conn->commit();
        
        return [
            'status' => 'success', 
            'message' => 'Leave application submitted successfully',
            'data' => [
                'days' => $days,
                'type' => $leaveType,
                'remaining_balance' => $balance[$leaveTypeBalance]
            ]
        ];
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        return ['status' => 'error', 'message' => $e->getMessage()];
    } finally {
        $stmt->close();
        $conn->close();
    }
}

// Get leave history for an employee
function getLeaveHistory($employeeId, $status = null, $type = null) {
    $conn = getDBConnection();
    
    $sql = "SELECT * FROM leave_log WHERE employee_id = ?";
    $params = [$employeeId];
    $types = "i";
    
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    if ($type) {
        $sql .= " AND leave_type = ?";
        $params[] = $type;
        $types .= "s";
    }
    
    $sql .= " ORDER BY date_applied DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
    return $history;
}

// Get pending leave request count
function getPendingRequestCount($employeeId) {
    $conn = getDBConnection();
    $sql = "SELECT COUNT(*) as count FROM leave_log WHERE employee_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employeeId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $result['count'];
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'apply':
            if (isset($_POST['employeeId'], $_POST['leaveType'], $_POST['startDate'], $_POST['endDate'], $_POST['reason'])) {
                $result = applyLeave(
                    $_POST['employeeId'],
                    $_POST['leaveType'],
                    $_POST['startDate'],
                    $_POST['endDate'],
                    $_POST['reason']
                );
                sendResponse($result['status'], $result['message'], $result['data'] ?? null);
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
            $status = $_GET['status'] ?? null;
            $type = $_GET['type'] ?? null;
            $history = getLeaveHistory($employeeId, $status, $type);
            sendResponse('success', 'Leave history retrieved', $history);
            break;
            
        case 'pending_count':
            $count = getPendingRequestCount($employeeId);
            sendResponse('success', 'Pending request count retrieved', $count);
            break;
            
        default:
            sendResponse('error', 'Invalid action');
    }
}
