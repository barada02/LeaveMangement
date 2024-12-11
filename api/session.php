<?php
session_start();

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
?>
