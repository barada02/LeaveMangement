<?php
require_once __DIR__ . '/config.php';

// Test database connection
$conn = getDBConnection();
if (!$conn) {
    die("Connection failed");
}

// Test query
$query = "SELECT COUNT(*) as count FROM employee_details";
$result = $conn->query($query);
$row = $result->fetch_assoc();
echo "Total employees: " . $row['count'] . "\n\n";

// Test full query
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
    die("Query failed: " . $conn->error);
}

echo "Employee details:\n";
while ($row = $result->fetch_assoc()) {
    print_r($row);
    echo "\n";
}

$conn->close();
?>
