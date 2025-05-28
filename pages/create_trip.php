<?php
require_once '../config/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all destinations
$destinations_query = "SELECT destination_id, destination_name FROM destinations ORDER BY destination_name";
$destinations_stmt = $pdo->query($destinations_query);
$destinations = $destinations_stmt->fetchAll();

// Fetch all hotels
$hotels_query = "SELECT hotel_id, hotel_name, hotel_location, star_rating FROM hotels ORDER BY hotel_name";
$hotels_stmt = $pdo->query($hotels_query);
$hotels = $hotels_stmt->fetchAll();

// Fetch all travel types
$travel_types_query = "SELECT travel_type_id, travel_type_name FROM travel_type ORDER BY travel_type_name";
$travel_types_stmt = $pdo->query($travel_types_query);
$travel_types = $travel_types_stmt->fetchAll();

// Fetch all itineraries
$itineraries_query = "SELECT itinerary_id, itinerary_activity, itineraray_time FROM itineraries ORDER BY itineraray_time";
$itineraries_stmt = $pdo->query($itineraries_query);
$itineraries = $itineraries_stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Store trip details in session
        $_SESSION['pending_trip'] = [
            'hotel_id' => $_POST['hotel_id'],
            'travel_type_id' => $_POST['travel_type_id'],
            'destination_id' => $_POST['destination_id'],
            'departure_date' => $_POST['departure_date'],
            'return_date' => $_POST['return_date'],
            'total_passengers' => $_POST['total_passengers'],
            'budget' => $_POST['budget'],
            'itinerary_id' => $_POST['itinerary_id']
        ];

        // Redirect to review page
        header('Location: review_trip.php?new=true');
        exit();
    } catch (PDOException $e) {
        $error = "Error processing trip: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Trip - Travelista</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/create_trip.css">
    <link rel="icon" href="../assets/images/logo.png">

</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="create-trip-container">
        <div class="form-container">
            <div class="form-header">
                <i class="fas fa-plane-departure"></i>
                <h1>Create New Trip</h1>
                <p class="form-subtitle">Plan your perfect adventure with us</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="trip-form">
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-map-marker-alt"></i>
                        <h2>Destination Details</h2>
                    </div>
                    <div class="form-group">
                        <label for="destination_id">
                            <i class="fas fa-globe-americas"></i> Destination
                        </label>
                        <select name="destination_id" id="destination_id" class="form-control" required>
                            <option value="">Select Destination</option>
                            <?php foreach ($destinations as $destination): ?>
                                <option value="<?php echo $destination['destination_id']; ?>">
                                    <?php echo htmlspecialchars($destination['destination_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="hotel_id">
                            <i class="fas fa-hotel"></i> Hotel
                        </label>
                        <select name="hotel_id" id="hotel_id" class="form-control" required>
                            <option value="">Select Hotel</option>
                            <?php foreach ($hotels as $hotel): ?>
                                <option value="<?php echo $hotel['hotel_id']; ?>">
                                    <?php echo htmlspecialchars($hotel['hotel_name'] . ' - ' . $hotel['hotel_location']); ?>
                                    <?php for ($i = 0; $i < $hotel['star_rating']; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-calendar-alt"></i>
                        <h2>Trip Schedule</h2>
                    </div>
                    <div class="form-group">
                        <label for="travel_type_id">
                            <i class="fas fa-route"></i> Travel Type
                        </label>
                        <select name="travel_type_id" id="travel_type_id" class="form-control" required>
                            <option value="">Select Travel Type</option>
                            <?php foreach ($travel_types as $type): ?>
                                <option value="<?php echo $type['travel_type_id']; ?>">
                                    <?php echo htmlspecialchars($type['travel_type_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="itinerary_id">
                            <i class="fas fa-list-check"></i> Itinerary
                        </label>
                        <select name="itinerary_id" id="itinerary_id" class="form-control" required>
                            <option value="">Select Itinerary</option>
                            <?php foreach ($itineraries as $itinerary): ?>
                                <option value="<?php echo $itinerary['itinerary_id']; ?>">
                                    <?php echo htmlspecialchars($itinerary['itinerary_activity'] . ' - ' . $itinerary['itineraray_time']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="departure_date">
                                <i class="fas fa-plane-departure"></i> Departure Date
                            </label>
                            <input type="date" name="departure_date" id="departure_date" class="form-control" required
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="return_date">
                                <i class="fas fa-plane-arrival"></i> Return Date
                            </label>
                            <input type="date" name="return_date" id="return_date" class="form-control" required
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-users"></i>
                        <h2>Trip Details</h2>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="total_passengers">
                                <i class="fas fa-user-friends"></i> Number of Passengers
                            </label>
                            <div class="input-with-icon">
                                <input type="number" name="total_passengers" id="total_passengers" class="form-control" 
                                       min="1" max="10" required>
                                <span class="input-icon"><i class="fas fa-users"></i></span>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="budget">
                                <i class="fas fa-dollar-sign"></i> Budget ($)
                            </label>
                            <div class="input-with-icon">
                                <input type="number" name="budget" id="budget" class="form-control" 
                                       min="0" step="100" required>
                                <span class="input-icon"><i class="fas fa-wallet"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i> Create Trip
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-times-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS for date validation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const departureDate = document.getElementById('departure_date');
            const returnDate = document.getElementById('return_date');

            // Date validation
            departureDate.addEventListener('change', function() {
                returnDate.min = this.value;
                if (returnDate.value && returnDate.value < this.value) {
                    returnDate.value = this.value;
                }
            });

            returnDate.addEventListener('change', function() {
                if (this.value < departureDate.value) {
                    alert('Return date must be after departure date');
                    this.value = departureDate.value;
                }
            });

            // Add animation to form sections
            const sections = document.querySelectorAll('.form-section');
            sections.forEach((section, index) => {
                section.style.animationDelay = `${index * 0.2}s`;
                section.classList.add('animate-in');
            });
        });
    </script>
</body>
</html> 