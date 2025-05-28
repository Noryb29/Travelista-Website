<?php
require_once '../config/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if we have pending trip data
if (!isset($_SESSION['pending_trip'])) {
    header('Location: create_trip.php');
    exit();
}

// Fetch trip details for confirmation
$trip_query = "SELECT d.destination_name, d.destination_img, d.destination_desc,
                      h.hotel_name, h.hotel_location, h.star_rating, h.hotel_img,
                      t.travel_type_name, i.itinerary_activity, i.itineraray_time
               FROM destinations d
               JOIN hotels h ON h.hotel_id = :hotel_id
               JOIN travel_type t ON t.travel_type_id = :travel_type_id
               JOIN itineraries i ON i.itinerary_id = :itinerary_id
               WHERE d.destination_id = :destination_id";

$trip_stmt = $pdo->prepare($trip_query);
$trip_stmt->execute([
    ':hotel_id' => $_SESSION['pending_trip']['hotel_id'],
    ':travel_type_id' => $_SESSION['pending_trip']['travel_type_id'],
    ':itinerary_id' => $_SESSION['pending_trip']['itinerary_id'],
    ':destination_id' => $_SESSION['pending_trip']['destination_id']
]);
$trip = $trip_stmt->fetch(PDO::FETCH_ASSOC);

// Add pending trip details
$trip['departure_date'] = $_SESSION['pending_trip']['departure_date'];
$trip['return_date'] = $_SESSION['pending_trip']['return_date'];
$trip['total_passengers'] = $_SESSION['pending_trip']['total_passengers'];
$trip['budget'] = $_SESSION['pending_trip']['budget'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO bookings_by_user (
            user_id, hotel_id, travel_type_id, destination_id, 
            departure_date, return_date, total_passengers, budget,
            itinerary_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['pending_trip']['hotel_id'],
            $_SESSION['pending_trip']['travel_type_id'],
            $_SESSION['pending_trip']['destination_id'],
            $_SESSION['pending_trip']['departure_date'],
            $_SESSION['pending_trip']['return_date'],
            $_SESSION['pending_trip']['total_passengers'],
            $_SESSION['pending_trip']['budget'],
            $_SESSION['pending_trip']['itinerary_id']
        ]);

        // Clear pending trip data
        unset($_SESSION['pending_trip']);

        header('Location: dashboard.php');
        exit();
    } catch (PDOException $e) {
        $error = "Error creating trip: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Trip - Travelista</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/review_trip.css">
    <link rel="icon" href="../assets/images/logo.png">

</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="review-container">
        <div class="review-content">
            <div class="trip-details">
                <div class="destination-section">
                    <div class="destination-image">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($trip['destination_img']); ?>" 
                             alt="<?php echo htmlspecialchars($trip['destination_name']); ?>">
                    </div>
                    <div class="destination-info">
                        <h1><?php echo htmlspecialchars($trip['destination_name']); ?></h1>
                        <p class="destination-desc"><?php echo htmlspecialchars($trip['destination_desc']); ?></p>
                    </div>
                </div>

                <div class="hotel-section">
                    <div class="hotel-image">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($trip['hotel_img']); ?>" 
                             alt="<?php echo htmlspecialchars($trip['hotel_name']); ?>">
                        <div class="hotel-rating">
                            <?php for ($i = 0; $i < $trip['star_rating']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="hotel-info">
                        <h2><?php echo htmlspecialchars($trip['hotel_name']); ?></h2>
                        <p class="hotel-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($trip['hotel_location']); ?>
                        </p>
                    </div>
                </div>

                <div class="trip-info">
                    <div class="info-card">
                        <i class="fas fa-route"></i>
                        <h3>Travel Type</h3>
                        <p><?php echo htmlspecialchars($trip['travel_type_name']); ?></p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-calendar-alt"></i>
                        <h3>Dates</h3>
                        <p>
                            <?php 
                            echo date('M d, Y', strtotime($trip['departure_date'])) . ' - ' . 
                                 date('M d, Y', strtotime($trip['return_date'])); 
                            ?>
                        </p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-list-check"></i>
                        <h3>Itinerary</h3>
                        <p><?php echo htmlspecialchars($trip['itinerary_activity']); ?></p>
                        <small><?php echo htmlspecialchars($trip['itineraray_time']); ?></small>
                    </div>
                </div>

                <div class="trip-info">
                    <div class="info-card">
                        <i class="fas fa-users"></i>
                        <h3>Passengers</h3>
                        <p><?php echo htmlspecialchars($trip['total_passengers']); ?> people</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-dollar-sign"></i>
                        <h3>Budget</h3>
                        <p>$<?php echo number_format($trip['budget'], 2); ?></p>
                    </div>
                </div>
            </div>

            <div class="review-form">
                <h2>Confirm Your Trip</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="review-form-content">
                    <div class="form-group">
                        <p class="confirmation-text">
                            Please review your trip details above. Once confirmed, your trip will be created and you'll be redirected to your dashboard.
                        </p>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i> Confirm Trip
                        </button>
                        <a href="create_trip.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Back to Edit
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 