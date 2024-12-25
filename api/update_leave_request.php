<?php
require_once __DIR__ . '/config.php';

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['request_id']) || !isset($data['action']) || !isset($data['admin_id'])) {
        throw new Exception('Request ID, action, and admin ID are required');
    }

    $leave_log_id = $data['request_id'];
    $action = strtolower($data['action']);
    $admin_id = $data['admin_id'];
    $note = isset($data['note']) && !empty($data['note']) ? $data['note'] : 
        ($action === 'approve' ? 'Approved by admin' : 'Rejected by admin');
    
    if (!in_array($action, ['approve', 'reject'])) {
        throw new Exception('Invalid action');
    }

    // Get database connection
    $conn = getDBConnection();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First, get the leave request details
        $query = "
            SELECT 
                l.*,
                e.name as employee_name
            FROM leave_log l
            JOIN employee_details e ON l.employee_id = e.employee_id
            WHERE l.leave_log_id = ? AND l.status = 'pending'
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $leave_log_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Leave request not found or already processed');
        }
        
        $leave_request = $result->fetch_assoc();
        
        // Calculate number of days
        $days = (strtotime($leave_request['end_date']) - strtotime($leave_request['start_date'])) / (60 * 60 * 24) + 1;
        
        // Update leave_log status
        $new_status = $action === 'approve' ? 'approved' : 'rejected';
        $update_query = "
            UPDATE leave_log 
            SET 
                status = ?,
                manager_id = ?,
                date_updated = NOW(),
                note = ?
            WHERE leave_log_id = ?
        ";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('sisi', $new_status, $admin_id, $note, $leave_log_id);
        $stmt->execute();
        
        // If approved, update leave_balance
        if ($action === 'approve') {
            // First get the current leave balance
            $balance_query = "
                SELECT * FROM leave_balance 
                WHERE employee_id = ?
            ";
            
            $stmt = $conn->prepare($balance_query);
            $stmt->bind_param('i', $leave_request['employee_id']);
            $stmt->execute();
            $balance_result = $stmt->get_result();
            
            if ($balance_result->num_rows === 0) {
                throw new Exception('Leave balance not found for employee');
            }
            
            $balance = $balance_result->fetch_assoc();
            
            // Update the specific leave type and total leaves
            $leave_type = $leave_request['leave_type'] . '_leave';
            $current_leave = $balance[$leave_type];
            
            if ($days > $current_leave) {
                throw new Exception('Insufficient leave balance');
            }
            
            $update_balance_query = "
                UPDATE leave_balance 
                SET 
                    {$leave_type} = {$leave_type} - ?,
                    total_leave_left = total_leave_left - ?
                WHERE employee_id = ?
            ";
            
            $stmt = $conn->prepare($update_balance_query);
            $stmt->bind_param('ddi', $days, $days, $leave_request['employee_id']);
            $stmt->execute();
            
            if ($stmt->affected_rows === 0) {
                throw new Exception('Failed to update leave balance');
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'message' => "Leave request " . ucfirst($new_status) . " successfully",
            'data' => null
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in update_leave_request.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => false,
        'message' => 'Error updating leave request: ' . $e->getMessage(),
        'data' => null
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
