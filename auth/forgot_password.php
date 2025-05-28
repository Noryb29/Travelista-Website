<?php
require_once '../config/config.php';
require_once '../alerts.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Travelista</title>
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
            
            <h2 class="text-center mb-4">Reset Your Password</h2>
            <p class="text-center text-muted mb-4">Enter your email address and we'll send you instructions to reset your password.</p>

            <form id="forgotPasswordForm" method="POST">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                </button>

                <div class="text-center mt-3">
                    <a href="login.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php include_once '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('forgotPasswordForm');
            if (!form) {
                console.error('Form not found');
                return;
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const email = document.getElementById('email').value.trim();

                if (!email) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please enter your email address',
                        confirmButtonColor: '#6B73FF'
                    });
                    return;
                }

                const formData = new FormData(form);

                Swal.fire({
                    title: 'Sending Reset Link...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('../controller/forgot_password_controller.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text()) // Use text() to inspect raw response
                .then(text => {
                    try {
                        const data = JSON.parse(text);

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
                    } catch (error) {
                        console.error('Invalid JSON:', text);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Unexpected response from server. Please try again later.',
                            confirmButtonColor: '#6B73FF'
                        });
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong! Please try again later.',
                        confirmButtonColor: '#6B73FF'
                    });
                });
            });
        });
    </script>
</body>
</html>
