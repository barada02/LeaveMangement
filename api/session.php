<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user']);
}

// Get current user data
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

// Set user session
function setUserSession($userData) {
    $_SESSION['user'] = $userData;
}

// Clear user session
function clearUserSession() {
    session_unset();
    session_destroy();
}

// Function to check session status
function checkSession() {
    if (!isLoggedIn()) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
        exit;
    }
    return getCurrentUser();
}
?>
