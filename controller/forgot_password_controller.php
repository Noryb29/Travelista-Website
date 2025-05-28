<?php
ob_start(); // Start output buffering at the very beginning (no whitespace above this)

require_once '../config/config.php';
require_once '../vendor/autoload.php';
require_once '../alerts.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Ensure JSON response header
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

try {
    // Ensure password_resets table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires DATETIME NOT NULL,
        used TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        INDEX (token),
        INDEX (expires)
    )");

    // Find user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'No account found with this email address']);
        exit;
    }

    // Check existing token
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE user_id = ? AND used = 0 AND expires > NOW()");
    $stmt->execute([$user['user_id']]);
    $existingReset = $stmt->fetch();

    if ($existingReset) {
        $token = $existingReset['token'];
    } else {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires) VALUES (?, ?, ?)");
        $stmt->execute([$user['user_id'], $token, $expires]);
    }

    // PHPMailer config
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // Enable verbose debug output if needed
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'esabarabar@gmail.com';
        $mail->Password = 'gpsf pkkd widc zlux';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        // Additional security settings
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Debug logging
        error_log("Attempting to send password reset email to: " . $email);

        $mail->setFrom('esabarabar@gmail.com', 'Travelista');
        $mail->addAddress($email, $user['username'] ?? '');
        $mail->addReplyTo('esabarabar@gmail.com', 'Travelista Support');

        // Improved URL construction
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://";
        $baseUrl .= $_SERVER['HTTP_HOST'];
        $baseUrl .= dirname(dirname($_SERVER['PHP_SELF'])); // Get the parent directory
        $resetLink = $baseUrl . "/auth/reset_password.php?token=" . $token;

        // Debug logging
        error_log("Generated reset link: " . $resetLink);

        // Compose email
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - Travelista';
        $mail->Body = <<<HTML
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2 style="color: #6B73FF;">Password Reset Request</h2>
                <p>Hello {$user['username']},</p>
                <p>We received a request to reset your password. Click the button below to reset it:</p>
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{$resetLink}" style="background-color: #6B73FF; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Password</a>
                </div>
                <p>If the button doesn't work, copy and paste this link in your browser:</p>
                <p style="word-break: break-all;">{$resetLink}</p>
                <p><strong>Important:</strong> This link will expire in 1 hour.</p>
                <p>If you didn't request this password reset, please ignore this email or contact support if you have concerns.</p>
                <hr style="border: 1px solid #eee; margin: 20px 0;">
                <p style="color: #666; font-size: 12px;">This is an automated message, please do not reply to this email.</p>
            </div>
        </body>
        </html>
        HTML;

        $mail->AltBody = "Reset your password using this link: {$resetLink}";

        $mail->send();
        error_log("Password reset email sent successfully to: " . $email);

        echo json_encode(['success' => true, 'message' => 'Password reset instructions have been sent to your email']);

    } catch (Exception $e) {
        error_log("Failed to send password reset email: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send reset instructions. Please try again later.'
        ]);
    }

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error processing request. Please try again.']);
}

ob_end_flush();
?> 