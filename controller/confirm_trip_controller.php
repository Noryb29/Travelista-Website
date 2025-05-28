<?php
require_once '../config/config.php';
require_once '../alerts.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to book a trip'
    ]);
    exit();
}

// Check if trip details exist in session
if (!isset($_SESSION['trip_details'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No trip selected. Please select a trip first.'
    ]);
    exit();
}

// Validate required fields
$required_fields = ['departure_date', 'return_date', 'total_passengers', 'budget'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields'
        ]);
        exit();
    }
}

try {
    // Get and validate dates
    $departure_date = new DateTime($_POST['departure_date']);
    $return_date = new DateTime($_POST['return_date']);
    $today = new DateTime();

    // Validate dates
    if ($departure_date < $today) {
        echo json_encode([
            'success' => false,
            'message' => 'Departure date cannot be in the past'
        ]);
        exit();
    }

    if ($return_date <= $departure_date) {
        echo json_encode([
            'success' => false,
            'message' => 'Return date must be after departure date'
        ]);
        exit();
    }

    // Validate passengers
    $total_passengers = (int)$_POST['total_passengers'];
    if ($total_passengers < 1 || $total_passengers > 10) {
        echo json_encode([
            'success' => false,
            'message' => 'Number of passengers must be between 1 and 10'
        ]);
        exit();
    }

    // Validate budget
    $budget = (float)$_POST['budget'];
    $total_price = $_SESSION['trip_details']['hotel_price'] + $_SESSION['trip_details']['travel_type_price'];
    if ($budget < 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Budget cannot be negative'
        ]);
        exit();
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Insert booking
    $stmt = $pdo->prepare("
        INSERT INTO bookings_by_user (
            user_id, 
            destination_id, 
            hotel_id, 
            travel_type_id, 
            departure_date, 
            return_date, 
            total_passengers, 
            budget, 
            booking_date
        ) VALUES (
            :user_id,
            :destination_id,
            :hotel_id,
            :travel_type_id,
            :departure_date,
            :return_date,
            :total_passengers,
            :budget,
            NOW()
        )
    ");

    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':destination_id' => $_SESSION['trip_details']['destination_id'],
        ':hotel_id' => $_SESSION['trip_details']['hotel_id'],
        ':travel_type_id' => $_SESSION['trip_details']['travel_type_id'],
        ':departure_date' => $departure_date->format('Y-m-d'),
        ':return_date' => $return_date->format('Y-m-d'),
        ':total_passengers' => $total_passengers,
        ':budget' => $budget
    ]);

    // Get the booking ID
    $booking_id = $pdo->lastInsertId();

    // Commit transaction
    $pdo->commit();

    // Clear trip details from session
    unset($_SESSION['trip_details']);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Trip booked successfully!',
        'booking_id' => $booking_id
    ]);

} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log error
    error_log("Booking Error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while booking your trip. Please try again later.'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log error
    error_log("General Error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again later.'
    ]);
}
?> 