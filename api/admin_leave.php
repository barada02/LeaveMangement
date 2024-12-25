<?php
// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/admin_error.log');

require_once 'config.php';
require_once 'session.php';

// Helper function to send JSON response
function sendJsonResponse($status, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Get pending leave requests
function getPendingRequests() {
    $conn = getDBConnection();
    
    $sql = "SELECT l.*, e.name as employee_name 
            FROM leave_log l 
            INNER JOIN employee_details e ON l.employee_id = e.employee_id 
            WHERE l.status = 'pending' 
            ORDER BY l.date_applied DESC";
    
    $result = $conn->query($sql);
    if (!$result) {
        error_log("Query failed: " . $conn->error);
        throw new Exception("Failed to fetch pending requests");
    }
    
    $requests = $result->fetch_all(MYSQLI_ASSOC);
    $conn->close();
    return $requests;
}

// Update leave request status
function updateLeaveStatus($leaveId, $status, $managerId, $note = '') {
    $conn = getDBConnection();
    
    try {
        $conn->begin_transaction();
        
        // Get leave request details
        $stmt = $conn->prepare("SELECT employee_id, leave_type, start_date, end_date FROM leave_log WHERE leave_log_id = ?");
        $stmt->bind_param("i", $leaveId);
        $stmt->execute();
        $result = $stmt->get_result();
        $leaveRequest = $result->fetch_assoc();
        
        if (!$leaveRequest) {
            throw new Exception("Leave request not found");
        }
        
        // Update status
        $stmt = $conn->prepare("UPDATE leave_log SET status = ?, manager_id = ?, manager_note = ?, action_date = NOW() WHERE leave_log_id = ?");
        $stmt->bind_param("sisi", $status, $managerId, $note, $leaveId);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update status");
        }
        
        // Update leave balance if approved
        if ($status === 'approved') {
            $startDate = new DateTime($leaveRequest['start_date']);
            $endDate = new DateTime($leaveRequest['end_date']);
            $days = $endDate->diff($startDate)->days + 1;
            
            $stmt = $conn->prepare("UPDATE leave_balance 
                                  SET used_days = used_days + ? 
                                  WHERE employee_id = ? AND leave_type = ?");
            $stmt->bind_param("iss", $days, $leaveRequest['employee_id'], $leaveRequest['leave_type']);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update leave balance");
            }
        }
        
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    } finally {
        $conn->close();
    }
}

// Handle API requests
try {
    session_start();
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'pending':
            try {
                $requests = getPendingRequests();
                sendJsonResponse(true, 'Success', $requests);
            } catch (Exception $e) {
                sendJsonResponse(false, $e->getMessage());
            }
            break;
            
        case 'update':
            if (!isset($_POST['leave_id']) || !isset($_POST['status'])) {
                sendJsonResponse(false, 'Missing required parameters');
            }
            
            try {
                $leaveId = $_POST['leave_id'];
                $status = $_POST['status'];
                $note = $_POST['note'] ?? '';
                $managerId = $_SESSION['user_id'];
                
                updateLeaveStatus($leaveId, $status, $managerId, $note);
                sendJsonResponse(true, 'Leave request updated successfully');
            } catch (Exception $e) {
                sendJsonResponse(false, $e->getMessage());
            }
            break;
            
        default:
            sendJsonResponse(false, 'Invalid action');
    }
} catch (Exception $e) {
    sendJsonResponse(false, 'An unexpected error occurred');
}
?>
