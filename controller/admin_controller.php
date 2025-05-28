<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if user exists in admin table
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM admin WHERE admin_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGetRequest();
        break;
    case 'POST':
        handlePostRequest();
        break;
    case 'DELETE':
        handleDeleteRequest();
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        break;
}

function handleGetRequest() {
    global $pdo;
    
    if (!isset($_GET['section'])) {
        echo json_encode(['success' => false, 'message' => 'Section not specified']);
        return;
    }

    $section = $_GET['section'];
    $data = [];

    switch ($section) {
        case 'dashboard':
            $data = getDashboardStats();
            break;
        case 'users':
            $data = getUsers();
            break;
        case 'destinations':
            $data = getDestinations();
            break;
        case 'hotels':
            $data = getHotels();
            break;
        case 'travel-types':
            $data = getTravelTypes();
            break;
        case 'bookings':
            $data = getBookings();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid section']);
            return;
    }

    echo json_encode(['success' => true, 'data' => $data]);
}

function handlePostRequest() {
    if (!isset($_POST['action'])) {
        echo json_encode(['success' => false, 'message' => 'Action not specified']);
        return;
    }

    $action = $_POST['action'];
    $result = false;

    switch ($action) {
        case 'add_user':
            $result = addUser();
            break;
        case 'edit_user':
            $result = editUser();
            break;
        case 'add_destination':
            $result = addDestination();
            break;
        case 'edit_destination':
            $result = editDestination();
            break;
        case 'add_hotel':
            $result = addHotel();
            break;
        case 'edit_hotel':
            $result = editHotel();
            break;
        case 'add_travel_type':
            $result = addTravelType();
            break;
        case 'edit_travel_type':
            $result = editTravelType();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            return;
    }

    echo json_encode($result);
}

function handleDeleteRequest() {
    if (!isset($_GET['action']) || !isset($_GET['type']) || !isset($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }

    $type = $_GET['type'];
    $id = $_GET['id'];
    $result = false;

    switch ($type) {
        case 'user':
            $result = deleteUser($id);
            break;
        case 'destination':
            $result = deleteDestination($id);
            break;
        case 'hotel':
            $result = deleteHotel($id);
            break;
        case 'travel_type':
            $result = deleteTravelType($id);
            break;
        case 'booking':
            $result = deleteBooking($id);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid type']);
            return;
    }

    echo json_encode($result);
}

// Dashboard functions
function getDashboardStats() {
    global $pdo;
    
    $stats = [
        'total_users' => 0,
        'total_destinations' => 0,
        'total_hotels' => 0,
        'total_bookings' => 0,
        'recent_bookings' => []
    ];

    // Get total users
    $query = "SELECT COUNT(*) as count FROM users";
    $stmt = $pdo->query($query);
    $stats['total_users'] = $stmt->fetchColumn();

    // Get total destinations
    $query = "SELECT COUNT(*) as count FROM destinations";
    $stmt = $pdo->query($query);
    $stats['total_destinations'] = $stmt->fetchColumn();

    // Get total hotels
    $query = "SELECT COUNT(*) as count FROM hotels";
    $stmt = $pdo->query($query);
    $stats['total_hotels'] = $stmt->fetchColumn();

    // Get total bookings
    $query = "SELECT COUNT(*) as count FROM bookings";
    $stmt = $pdo->query($query);
    $stats['total_bookings'] = $stmt->fetchColumn();

    // Get recent bookings
    $query = "SELECT b.*, u.username, d.destination_name, h.hotel_name, t.travel_type_name 
              FROM bookings b 
              JOIN users u ON b.user_id = u.user_id 
              JOIN destinations d ON b.destination_id = d.destination_id 
              JOIN hotels h ON b.hotel_id = h.hotel_id 
              JOIN travel_types t ON b.travel_type_id = t.travel_type_id 
              ORDER BY b.created_at DESC LIMIT 5";
    $stmt = $pdo->query($query);
    $stats['recent_bookings'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $stats;
}

// User management functions
function getUsers() {
    global $pdo;
    
    $query = "SELECT user_id, username, firstname, lastname, email, is_admin, created_at FROM users";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addUser() {
    global $pdo;
    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }

    // Check if username or email already exists
    $query = "SELECT * FROM users WHERE username = :username OR email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['username' => $username, 'email' => $email]);
    
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Username or email already exists'];
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $query = "INSERT INTO users (username, email, password, is_admin) VALUES (:username, :email, :password, :is_admin)";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute([
        'username' => $username,
        'email' => $email,
        'password' => $hashed_password,
        'is_admin' => $is_admin
    ])) {
        return ['success' => true, 'message' => 'User added successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to add user'];
    }
}

function editUser() {
    global $pdo;
    
    $user_id = $_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // Validate input
    if (empty($username) || empty($email)) {
        return ['success' => false, 'message' => 'Username and email are required'];
    }

    // Check if username or email already exists for other users
    $query = "SELECT * FROM users WHERE (username = :username OR email = :email) AND user_id != :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'username' => $username,
        'email' => $email,
        'user_id' => $user_id
    ]);
    
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Username or email already exists'];
    }

    // Update user
    $query = "UPDATE users SET username = :username, email = :email, is_admin = :is_admin WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute([
        'username' => $username,
        'email' => $email,
        'is_admin' => $is_admin,
        'user_id' => $user_id
    ])) {
        return ['success' => true, 'message' => 'User updated successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to update user'];
    }
}

function deleteUser($user_id) {
    global $pdo;
    
    // Check if user has any bookings
    $query = "SELECT COUNT(*) FROM bookings WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $user_id]);
    
    if ($stmt->fetchColumn() > 0) {
        return ['success' => false, 'message' => 'Cannot delete user with existing bookings'];
    }

    // Delete user
    $query = "DELETE FROM users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute(['user_id' => $user_id])) {
        return ['success' => true, 'message' => 'User deleted successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to delete user'];
    }
}

// Destination management functions
function getDestinations() {
    global $pdo;
    
    $query = "SELECT * FROM destinations ORDER BY created_at DESC";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addDestination() {
    global $pdo;
    
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
    }

    if (empty($name) || empty($description)) {
        return ['success' => false, 'message' => 'Name and description are required'];
    }

    $query = "INSERT INTO destinations (destination_name, destination_desc, destination_img) VALUES (:name, :description, :image)";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute([
        'name' => $name,
        'description' => $description,
        'image' => $image
    ])) {
        return ['success' => true, 'message' => 'Destination added successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to add destination'];
    }
}

function editDestination() {
    global $pdo;
    
    $destination_id = $_POST['destination_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
    }

    if (empty($name) || empty($description)) {
        return ['success' => false, 'message' => 'Name and description are required'];
    }

    if ($image) {
        $query = "UPDATE destinations SET destination_name = :name, destination_desc = :description, destination_img = :image WHERE destination_id = :id";
        $params = [
            'name' => $name,
            'description' => $description,
            'image' => $image,
            'id' => $destination_id
        ];
    } else {
        $query = "UPDATE destinations SET destination_name = :name, destination_desc = :description WHERE destination_id = :id";
        $params = [
            'name' => $name,
            'description' => $description,
            'id' => $destination_id
        ];
    }
    
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute($params)) {
        return ['success' => true, 'message' => 'Destination updated successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to update destination'];
    }
}

function deleteDestination($destination_id) {
    global $pdo;
    
    // Check if destination has any bookings
    $query = "SELECT COUNT(*) FROM bookings WHERE destination_id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $destination_id]);
    
    if ($stmt->fetchColumn() > 0) {
        return ['success' => false, 'message' => 'Cannot delete destination with existing bookings'];
    }

    // Delete destination
    $query = "DELETE FROM destinations WHERE destination_id = :id";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute(['id' => $destination_id])) {
        return ['success' => true, 'message' => 'Destination deleted successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to delete destination'];
    }
}

// Hotel management functions
function getHotels() {
    global $pdo;
    
    $query = "SELECT * FROM hotels ORDER BY created_at DESC";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addHotel() {
    global $pdo;
    
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $star_rating = intval($_POST['star_rating']);
    
    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
    }

    if (empty($name) || empty($location) || empty($description) || $price <= 0 || $star_rating <= 0) {
        return ['success' => false, 'message' => 'All fields are required and must be valid'];
    }

    $query = "INSERT INTO hotels (hotel_name, hotel_location, hotel_desc, price, star_rating, hotel_img) 
              VALUES (:name, :location, :description, :price, :rating, :image)";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute([
        'name' => $name,
        'location' => $location,
        'description' => $description,
        'price' => $price,
        'rating' => $star_rating,
        'image' => $image
    ])) {
        return ['success' => true, 'message' => 'Hotel added successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to add hotel'];
    }
}

function editHotel() {
    global $pdo;
    
    $hotel_id = $_POST['hotel_id'];
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $star_rating = intval($_POST['star_rating']);
    
    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
    }

    if (empty($name) || empty($location) || empty($description) || $price <= 0 || $star_rating <= 0) {
        return ['success' => false, 'message' => 'All fields are required and must be valid'];
    }

    if ($image) {
        $query = "UPDATE hotels SET hotel_name = :name, hotel_location = :location, hotel_desc = :description, 
                  price = :price, star_rating = :rating, hotel_img = :image WHERE hotel_id = :id";
        $params = [
            'name' => $name,
            'location' => $location,
            'description' => $description,
            'price' => $price,
            'rating' => $star_rating,
            'image' => $image,
            'id' => $hotel_id
        ];
    } else {
        $query = "UPDATE hotels SET hotel_name = :name, hotel_location = :location, hotel_desc = :description, 
                  price = :price, star_rating = :rating WHERE hotel_id = :id";
        $params = [
            'name' => $name,
            'location' => $location,
            'description' => $description,
            'price' => $price,
            'rating' => $star_rating,
            'id' => $hotel_id
        ];
    }
    
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute($params)) {
        return ['success' => true, 'message' => 'Hotel updated successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to update hotel'];
    }
}

function deleteHotel($hotel_id) {
    global $pdo;
    
    // Check if hotel has any bookings
    $query = "SELECT COUNT(*) FROM bookings WHERE hotel_id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $hotel_id]);
    
    if ($stmt->fetchColumn() > 0) {
        return ['success' => false, 'message' => 'Cannot delete hotel with existing bookings'];
    }

    // Delete hotel
    $query = "DELETE FROM hotels WHERE hotel_id = :id";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute(['id' => $hotel_id])) {
        return ['success' => true, 'message' => 'Hotel deleted successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to delete hotel'];
    }
}

// Travel type management functions
function getTravelTypes() {
    global $pdo;
    
    $query = "SELECT * FROM travel_types ORDER BY created_at DESC";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addTravelType() {
    global $pdo;
    
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);

    if (empty($name) || $price <= 0) {
        return ['success' => false, 'message' => 'Name and price are required and must be valid'];
    }

    $query = "INSERT INTO travel_types (travel_type_name, travel_type_price) VALUES (:name, :price)";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute([
        'name' => $name,
        'price' => $price
    ])) {
        return ['success' => true, 'message' => 'Travel type added successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to add travel type'];
    }
}

function editTravelType() {
    global $pdo;
    
    $type_id = $_POST['type_id'];
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);

    if (empty($name) || $price <= 0) {
        return ['success' => false, 'message' => 'Name and price are required and must be valid'];
    }

    $query = "UPDATE travel_types SET travel_type_name = :name, travel_type_price = :price WHERE travel_type_id = :id";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute([
        'name' => $name,
        'price' => $price,
        'id' => $type_id
    ])) {
        return ['success' => true, 'message' => 'Travel type updated successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to update travel type'];
    }
}

function deleteTravelType($type_id) {
    global $pdo;
    
    // Check if travel type has any bookings
    $query = "SELECT COUNT(*) FROM bookings WHERE travel_type_id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $type_id]);
    
    if ($stmt->fetchColumn() > 0) {
        return ['success' => false, 'message' => 'Cannot delete travel type with existing bookings'];
    }

    // Delete travel type
    $query = "DELETE FROM travel_types WHERE travel_type_id = :id";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute(['id' => $type_id])) {
        return ['success' => true, 'message' => 'Travel type deleted successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to delete travel type'];
    }
}

// Booking management functions
function getBookings() {
    global $pdo;
    
    $query = "SELECT b.*, u.username, d.destination_name, h.hotel_name, t.travel_type_name 
              FROM bookings b 
              JOIN users u ON b.user_id = u.user_id 
              JOIN destinations d ON b.destination_id = d.destination_id 
              JOIN hotels h ON b.hotel_id = h.hotel_id 
              JOIN travel_types t ON b.travel_type_id = t.travel_type_id 
              ORDER BY b.created_at DESC";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteBooking($booking_id) {
    global $pdo;
    
    $query = "DELETE FROM bookings WHERE booking_id = :id";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute(['id' => $booking_id])) {
        return ['success' => true, 'message' => 'Booking deleted successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to delete booking'];
    }
}
?> 