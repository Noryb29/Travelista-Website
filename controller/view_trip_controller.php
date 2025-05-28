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
    $verify_stmt = $pdo->prepare("
        SELECT b.*, d.destination_name, d.destination_img, d.destination_desc,
               h.hotel_name, h.hotel_location, h.star_rating, h.hotel_img, h.price as hotel_price,
               t.travel_type_name, t.travel_type_price
        FROM bookings_by_user b
        JOIN destinations d ON b.destination_id = d.destination_id
        JOIN hotels h ON b.hotel_id = h.hotel_id
        JOIN travel_type t ON b.travel_type_id = t.travel_type_id
        WHERE b.booking_id = ? AND b.user_id = ?
    ");
    $verify_stmt->execute([$booking_id, $user_id]);
    
    if ($verify_stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access'
        ]);
        exit;
    }

    $booking = $verify_stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate total price
    $total_price = $booking['hotel_price'] + $booking['travel_type_price'];

    // Format dates
    $departure_date = date('F d, Y', strtotime($booking['departure_date']));
    $return_date = date('F d, Y', strtotime($booking['return_date']));

    echo json_encode([
        'success' => true,
        'booking' => [
            'id' => $booking['booking_id'],
            'destination' => [
                'name' => $booking['destination_name'],
                'description' => $booking['destination_desc'],
                'image' => base64_encode($booking['destination_img'])
            ],
            'hotel' => [
                'name' => $booking['hotel_name'],
                'location' => $booking['hotel_location'],
                'rating' => $booking['star_rating'],
                'image' => base64_encode($booking['hotel_img']),
                'price' => $booking['hotel_price']
            ],
            'travel' => [
                'type' => $booking['travel_type_name'],
                'price' => $booking['travel_type_price']
            ],
            'dates' => [
                'departure' => $departure_date,
                'return' => $return_date
            ],
            'passengers' => $booking['total_passengers'],
            'budget' => $booking['budget'],
            'total_price' => $total_price
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching trip details: ' . $e->getMessage()
    ]);
} 