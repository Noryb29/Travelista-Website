<?php
require_once '../config/config.php';
require_once '../alerts.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear remember me token if exists
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    // Remove token from database
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
    $stmt->execute([$token]);
    
    // Remove cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Clear session
session_unset();
session_destroy();

// Return JSON response for AJAX
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully'
]);
exit();
?> 