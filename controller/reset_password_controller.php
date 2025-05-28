<?php
require_once '../config/config.php';
require_once '../alerts.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Enhanced input validation
if (empty($token)) {
    echo json_encode([
        'success' => false,
        'message' => 'Reset token is required'
    ]);
    exit;
}

if (empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'New password is required'
    ]);
    exit;
}

if (empty($confirm_password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please confirm your password'
    ]);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode([
        'success' => false,
        'message' => 'Passwords do not match'
    ]);
    exit;
}

// Enhanced password validation
if (strlen($password) < 8) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 8 characters long'
    ]);
    exit;
}

if (!preg_match('/[A-Z]/', $password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must contain at least one uppercase letter'
    ]);
    exit;
}

if (!preg_match('/[a-z]/', $password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must contain at least one lowercase letter'
    ]);
    exit;
}

if (!preg_match('/[0-9]/', $password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must contain at least one number'
    ]);
    exit;
}

if (!preg_match('/[@$!%*?&]/', $password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must contain at least one special character (@$!%*?&)'
    ]);
    exit;
}

try {
    // Debug logging
    error_log("Attempting to reset password with token: " . $token);
    
    // Verify token and get user
    $stmt = $pdo->prepare("
        SELECT pr.token, pr.user_id, pr.expires, pr.used, u.email 
        FROM password_resets pr 
        JOIN users u ON pr.user_id = u.user_id 
        WHERE pr.token = ? AND pr.expires > NOW() AND pr.used = 0
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        // Check if token exists but is expired or used
        $stmt = $pdo->prepare("
            SELECT pr.token, pr.expires, pr.used 
            FROM password_resets pr 
            WHERE pr.token = ?
        ");
        $stmt->execute([$token]);
        $tokenCheck = $stmt->fetch();
        
        if ($tokenCheck) {
            if ($tokenCheck['used'] == 1) {
                echo json_encode([
                    'success' => false,
                    'message' => 'This reset link has already been used. Please request a new password reset.'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'This reset link has expired. Please request a new password reset.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid reset link. Please request a new password reset.'
            ]);
        }
        exit;
    }

    // Update password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->execute([$hashed_password, $reset['user_id']]);

    // Mark token as used
    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
    $stmt->execute([$token]);

    // Debug logging
    error_log("Password successfully reset for user ID: " . $reset['user_id']);

    echo json_encode([
        'success' => true,
        'message' => 'Password has been reset successfully'
    ]);

} catch (PDOException $e) {
    error_log("Password reset error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again later.'
    ]);
} catch (Exception $e) {
    error_log("Unexpected error during password reset: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again later.'
    ]);
}
?> 