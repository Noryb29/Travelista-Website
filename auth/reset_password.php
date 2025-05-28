<?php
require_once '../config/config.php';
require_once '../alerts.php';

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$token = $_GET['token'] ?? '';
$valid = false;
$error = '';

if (!empty($token)) {
    try {
        // Debug: Log the token
        error_log("Attempting to validate token: " . $token);
        
        $stmt = $pdo->prepare("
            SELECT pr.token, pr.user_id, pr.expires, pr.used, u.email, u.username 
            FROM password_resets pr 
            JOIN users u ON pr.user_id = u.user_id 
            WHERE pr.token = ? AND pr.expires > NOW() AND pr.used = 0
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        // Debug: Log the query result
        error_log("Query result: " . print_r($reset, true));

        if ($reset) {
            $valid = true;
        } else {
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
                    $error = 'This reset link has already been used. Please request a new password reset.';
                } else if (strtotime($tokenCheck['expires']) < time()) {
                    $error = 'This reset link has expired. Please request a new password reset.';
                }
            } else {
                $error = 'Invalid reset link. Please request a new password reset.';
            }
        }
    } catch (PDOException $e) {
        error_log("Reset password error: " . $e->getMessage());
        $error = 'An error occurred. Please try again later.';
    }
} else {
    $error = 'Invalid reset link. Please request a new password reset.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Travelista</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="login-form">
            <div class="brand-logo">
                <img src="../assets/images/logo.png" alt="Travelista Logo">
                <h1>Travelista</h1>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if($valid): ?>
                <form method="POST" action="../controller/reset_password_controller.php" id="resetForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="form-floating mb-3 position-relative">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="New Password" required minlength="8"
                               pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$">
                        <label for="password">
                            <i class="fas fa-lock me-2"></i>New Password
                        </label>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                        <div class="password-requirements mt-2 small text-muted">
                            Password must contain:
                            <ul class="mb-0 ps-3">
                                <li id="length">At least 8 characters</li>
                                <li id="uppercase">One uppercase letter</li>
                                <li id="lowercase">One lowercase letter</li>
                                <li id="number">One number</li>
                                <li id="special">One special character</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-floating mb-3 position-relative">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm Password" required>
                        <label for="confirm_password">
                            <i class="fas fa-lock me-2"></i>Confirm Password
                        </label>
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password')"></i>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-key me-2"></i>Reset Password
                    </button>
                </form>
            <?php else: ?>
                <div class="text-center">
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = passwordField.nextElementSibling.nextElementSibling;
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const requirements = {
            length: /.{8,}/,
            uppercase: /[A-Z]/,
            lowercase: /[a-z]/,
            number: /[0-9]/,
            special: /[@$!%*?&]/
        };

        password.addEventListener('input', function() {
            const value = this.value;
            
            // Check each requirement
            for (const [requirement, regex] of Object.entries(requirements)) {
                const element = document.getElementById(requirement);
                if (regex.test(value)) {
                    element.style.color = 'green';
                    element.innerHTML = `<i class="fas fa-check"></i> ${element.textContent.split(' ').slice(1).join(' ')}`;
                } else {
                    element.style.color = 'inherit';
                    element.innerHTML = element.textContent.split(' ').slice(1).join(' ');
                }
            }
        });

        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Validate password requirements
            let isValid = true;
            for (const [requirement, regex] of Object.entries(requirements)) {
                if (!regex.test(password)) {
                    isValid = false;
                    break;
                }
            }

            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Password',
                    text: 'Please ensure your password meets all requirements',
                    confirmButtonColor: '#6B73FF'
                });
                return;
            }

            if (password !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'Passwords do not match',
                    confirmButtonColor: '#6B73FF'
                });
                return;
            }

            const formData = new FormData(this);
            
            Swal.fire({
                title: 'Resetting Password...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('../controller/reset_password_controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonColor: '#6B73FF'
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#6B73FF'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong! Please try again later.',
                    confirmButtonColor: '#6B73FF'
                });
            });
        });
    </script>

    <?php include_once '../includes/footer.php'; ?>
</body>
</html> 