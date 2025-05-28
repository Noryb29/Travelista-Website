<?php
session_start();
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

$booking_id = $_POST['booking_id'];
$user_id = $_SESSION['user_id'];

try {
    // Verify that the booking belongs to the user
    $verify_stmt = $pdo->prepare("SELECT user_id FROM bookings_by_user WHERE booking_id = ?");
    $verify_stmt->execute([$booking_id]);
    $booking = $verify_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking || $booking['user_id'] != $user_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access'
        ]);
        exit;
    }

    // Update the booking
    $update_stmt = $pdo->prepare("
        UPDATE bookings_by_user
        SET departure_date = ?,
            return_date = ?,
            total_passengers = ?,
            budget = ?
        WHERE booking_id = ? AND user_id = ?
    ");

    $success = $update_stmt->execute([
        $_POST['departure_date'],
        $_POST['return_date'],
        $_POST['total_passengers'],
        $_POST['budget'],
        $booking_id,
        $user_id
    ]);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Trip updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update trip'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating trip: ' . $e->getMessage()
    ]);
}