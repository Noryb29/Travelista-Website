<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$booking_id = $_GET['id'];

// Fetch available destinations, hotels, and travel types
$destinations_stmt = $pdo->query("SELECT destination_id, destination_name FROM destinations");
$destinations = $destinations_stmt->fetchAll(PDO::FETCH_ASSOC);

$hotels_stmt = $pdo->query("SELECT hotel_id, hotel_name, hotel_location, price FROM hotels");
$hotels = $hotels_stmt->fetchAll(PDO::FETCH_ASSOC);

$travel_types_stmt = $pdo->query("SELECT travel_type_id, travel_type_name, travel_type_price FROM travel_type");
$travel_types = $travel_types_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Trip - Travelista</title>
    <link rel="stylesheet" href="../assets/css/edit_trip.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../assets/images/logo.png">

</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="edit-container">
        <div class="edit-header">
            <a href="view_trip.php?id=<?php echo $booking_id; ?>" class="back-btn">
                <i class='bx bx-arrow-back'></i> Back to Trip Details
            </a>
            <h1>Edit Your Trip</h1>
        </div>

        <div class="edit-content">
            <form id="editTripForm" class="edit-form">
                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                
                <div class="form-section">
                    <h3><i class='bx bx-map-alt'></i> Destination</h3>
                    <div class="form-group">
                        <label for="destination_id">Select Destination</label>
                        <select id="destination_id" name="destination_id" aria-placeholder="<?php echo $destination['destination_id'];?>" required>
                            <?php foreach ($destinations as $destination): ?>
                                <option value="<?php echo $destination['destination_id']; ?>">
                                    <?php echo htmlspecialchars($destination['destination_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class='bx bx-building-house'></i> Hotel</h3>
                    <div class="form-group">
                        <label for="hotel_id">Select Hotel</label>
                        <select id="hotel_id" name="hotel_id" required>
                            <?php foreach ($hotels as $hotel): ?>
                                <option value="<?php echo $hotel['hotel_id']; ?>" data-price="<?php echo $hotel['price']; ?>">
                                    <?php echo htmlspecialchars($hotel['hotel_name'] . ' - ' . $hotel['hotel_location'] . ' ($' . $hotel['price'] . ')');
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class='bx bx-car'></i> Travel Type</h3>
                    <div class="form-group">
                        <label for="travel_type_id">Select Travel Type</label>
                        <select id="travel_type_id" name="travel_type_id" required>
                            <?php foreach ($travel_types as $type): ?>
                                <option value="<?php echo $type['travel_type_id']; ?>" data-price="<?php echo $type['travel_type_price']; ?>">
                                    <?php echo htmlspecialchars($type['travel_type_name'] . ' ($' . $type['travel_type_price'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class='bx bx-calendar'></i> Travel Dates</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="departure_date">Departure Date</label>
                            <input type="date" id="departure_date" name="departure_date" required>
                        </div>
                        <div class="form-group">
                            <label for="return_date">Return Date</label>
                            <input type="date" id="return_date" name="return_date" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class='bx bx-group'></i> Travel Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="total_passengers">Number of Passengers</label>
                            <input type="number" id="total_passengers" name="total_passengers" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="budget">Budget</label>
                            <input type="number" id="budget" name="budget" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn cancel-btn" onclick="window.location.href='view_trip.php?id=<?php echo $booking_id; ?>'">
                        <i class='bx bx-x'></i> Cancel
                    </button>
                    <button type="submit" class="btn save-btn">
                        <i class='bx bx-check'></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch current trip details
        fetch(`../controller/view_trip_controller.php?id=<?php echo $booking_id; ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const booking = data.booking;
                    document.getElementById('destination_id').value = booking.destination.id;
                    document.getElementById('hotel_id').value = booking.hotel.id;
                    document.getElementById('travel_type_id').value = booking.travel.id;
                    document.getElementById('departure_date').value = new Date(booking.dates.departure).toISOString().split('T')[0];
                    document.getElementById('return_date').value = new Date(booking.dates.return).toISOString().split('T')[0];
                    document.getElementById('total_passengers').value = booking.passengers;
                    document.getElementById('budget').value = booking.budget;
                }
            })
            .catch(error => console.error('Error:', error));

        // Handle form submission
        document.getElementById('editTripForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('../controller/edit_trip_controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `view_trip.php?id=${formData.get('booking_id')}`;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
    </script>
</body>
</html>