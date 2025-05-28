<?php
require_once '../config/config.php';
require_once '../alerts.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Handle profile image upload
    $profile_img = null;
    if(isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_img']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($file_ext, $allowed)) {
            $upload_path = '../uploads/profiles/';
            if(!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            
            $new_filename = uniqid('profile_') . '.' . $file_ext;
            if(move_uploaded_file($_FILES['profile_img']['tmp_name'], $upload_path . $new_filename)) {
                $profile_img = '../uploads/profiles/' . $new_filename;
            }
        }
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        setAlert('Registration Failed', 'Email already exists!', 'error');
    } else {
        // Insert new user
        $sql = "INSERT INTO users (username, firstname, lastname, gender, email, password, profile_img) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute([$username, $firstname, $lastname, $gender, $email, $password, $profile_img]);
            setAlert('Welcome to Travelista!', 'Your account has been created successfully', 'success', [
                'then' => 'window.location.href = "../pages/dashboard.php"'
            ]);
            header("Location: ../pages/dashboard.php");
            exit();
        } catch(PDOException $e) {
            setAlert('Registration Failed', 'Error creating account: ' . $e->getMessage(), 'error');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Travelista</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="registration-form">
                <h2 class="text-center">Create Your Account</h2>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data" id="registrationForm">
                    <div class="profile-upload">
                        <div class="profile-preview" onclick="document.getElementById('profile_img').click()">
                            <img id="preview-image" src="../assets/images/default-profile.jpg" alt="Profile Preview">
                            <div class="upload-icon">
                                <i class="fas fa-camera"></i>
                            </div>
                        </div>
                        <input type="file" class="d-none" id="profile_img" name="profile_img" accept="image/*">
                        <small class="text-muted">Click to upload profile picture</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                                <label for="username"><i class="fas fa-user me-2"></i>Username</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                                <label for="email"><i class="fas fa-envelope me-2"></i>Email address</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="firstname" name="firstname" placeholder="First Name" required>
                                <label for="firstname"><i class="fas fa-user me-2"></i>First Name</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Last Name" required>
                                <label for="lastname"><i class="fas fa-user me-2"></i>Last Name</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-floating">
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <label for="gender"><i class="fas fa-venus-mars me-2"></i>Gender</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                        </div>
                        <div class="form-text">Password must be at least 6 characters long</div>
                    </div>

                    <div class="mb-4">
                        <div class="form-floating">
                            <input type="password" class="form-control" id="confirm_password" placeholder="Confirm Password" required>
                            <label for="confirm_password"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password')"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
                    
                    <div class="text-center">
                        Already have an account? <a href="login.php" class="login-link">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once '../includes/footer.php'; ?>

    <script>
        // Preview uploaded image
        document.getElementById('profile_img').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                    document.querySelector('.upload-icon').style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const password = formData.get('password');
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Add confirm password to form data
            formData.append('confirm_password', confirmPassword);
            
            // Validate passwords match before submission
            if (password !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'Passwords do not match',
                    confirmButtonColor: '#6B73FF'
                });
                return;
            }
            
            // Show loading state
            Swal.fire({
                title: 'Creating Account...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('../controller/register_controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome to Travelista!',
                        text: data.message,
                        confirmButtonColor: '#6B73FF'
                    }).then(() => {
                        window.location.href = '../pages/dashboard.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Registration Failed',
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
</body>
</html> 