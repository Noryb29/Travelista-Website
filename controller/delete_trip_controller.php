<?php
require_once '../config/config.php';
require_once '../alerts.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to continue'
    ]);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Booking ID is required'
    ]);
    exit;
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Verify that the booking belongs to the user
    $verify_stmt = $pdo->prepare("SELECT booking_id FROM bookings_by_user WHERE booking_id = ? AND user_id = ?");
    $verify_stmt->execute([$booking_id, $user_id]);
    
    if ($verify_stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access'
        ]);
        exit;
    }

    // Delete the booking
    $delete_stmt = $pdo->prepare("DELETE FROM bookings_by_user WHERE booking_id = ?");
    $delete_stmt->execute([$booking_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Trip deleted successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting trip: ' . $e->getMessage()
    ]);
} 