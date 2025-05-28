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

// Get form data
$username = trim($_POST['username'] ?? '');
$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$gender = $_POST['gender'] ?? '';
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate required fields
$required_fields = [
    'username' => 'Username',
    'firstname' => 'First Name',
    'lastname' => 'Last Name',
    'gender' => 'Gender',
    'email' => 'Email',
    'password' => 'Password',
    'confirm_password' => 'Confirm Password'
];

foreach ($required_fields as $field => $label) {
    if (empty($$field)) {
        echo json_encode([
            'success' => false,
            'message' => $label . ' is required'
        ]);
        exit;
    }
}

// Validate username (alphanumeric and underscore only)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    echo json_encode([
        'success' => false,
        'message' => 'Username can only contain letters, numbers, and underscores'
    ]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters long'
    ]);
    exit;
}

// Validate password confirmation
if ($password !== $confirm_password) {
    echo json_encode([
        'success' => false,
        'message' => 'Passwords do not match'
    ]);
    exit;
}

try {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username already exists'
        ]);
        exit;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists'
        ]);
        exit;
    }

    // Handle profile image upload
    $profile_img = 'assets/images/default-profile.jpg'; // Default profile image
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_img']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowed)
            ]);
            exit;
        }

        $upload_path = '../uploads/profiles/';
        if (!is_dir($upload_path)) {
            if (!mkdir($upload_path, 0777, true)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to create upload directory'
                ]);
                exit;
            }
        }
        
        $new_filename = uniqid('profile_') . '.' . $file_ext;
        $full_path = $upload_path . $new_filename;
        
        if (!move_uploaded_file($_FILES['profile_img']['tmp_name'], $full_path)) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to upload profile image'
            ]);
            exit;
        }
        
        $profile_img = 'uploads/profiles/' . $new_filename;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (username, firstname, lastname, gender, email, password, profile_img) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $username,
        $firstname,
        $lastname,
        $gender,
        $email,
        $hashed_password,
        $profile_img
    ]);

    // Get the new user's ID
    $user_id = $pdo->lastInsertId();

    // Set user data in session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful'
    ]);

} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again later.'
    ]);
}
?> 