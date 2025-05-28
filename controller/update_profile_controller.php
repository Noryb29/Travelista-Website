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

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get form data
$username = trim($_POST['username'] ?? '');
$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$gender = $_POST['gender'] ?? '';
$email = trim($_POST['email'] ?? '');

// Validate required fields
$required_fields = [
    'username' => 'Username',
    'firstname' => 'First Name',
    'lastname' => 'Last Name',
    'gender' => 'Gender',
    'email' => 'Email'
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

try {
    // Check if username already exists (excluding current user)
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username already exists'
        ]);
        exit;
    }

    // Check if email already exists (excluding current user)
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already exists'
        ]);
        exit;
    }

    // Handle profile image upload
    $profile_img = null;
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

        // Read the file content
        $file_content = file_get_contents($_FILES['profile_img']['tmp_name']);
        if ($file_content === false) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to read uploaded file'
            ]);
            exit;
        }

        $profile_img = $file_content;
    }

    // Update user profile
    $sql = "UPDATE users SET 
            username = ?, 
            firstname = ?, 
            lastname = ?, 
            gender = ?, 
            email = ?";
    
    $params = [$username, $firstname, $lastname, $gender, $email];

    if ($profile_img) {
        $sql .= ", profile_img = ?";
        $params[] = $profile_img;
    }

    $sql .= " WHERE user_id = ?";
    $params[] = $user_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Update session username
    $_SESSION['username'] = $username;

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully'
    ]);

} catch (PDOException $e) {
    error_log("Profile update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update profile. Please try again later.'
    ]);
}
?> 