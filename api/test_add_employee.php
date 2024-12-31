<?php
// Test data
$test_data = [
    'name' => 'Test Employee',
    'email' => 'test.employee' . time() . '@example.com',
    'department' => 'IT',
    'date_joined' => date('Y-m-d'),
    'username' => 'testuser' . time(),
    'password' => 'Test@123',
    'petname' => 'Buddy',
    'role' => 'employee'
];

// Convert to JSON
$json_data = json_encode($test_data);

// Set up cURL
$ch = curl_init('http://localhost/leavems/LeaveMangement/api/add_employee.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_data)
]);

// Execute request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);

// Output results
echo "HTTP Status Code: " . $http_code . "\n";
if ($curl_error) {
    echo "cURL Error: " . $curl_error . "\n";
}
echo "Response: " . $response . "\n";

// Decode and pretty print JSON response if valid
if ($response) {
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo "\nDecoded Response:\n";
        echo json_encode($decoded, JSON_PRETTY_PRINT);
    }
}

curl_close($ch);
?>
