<?php
include '../includes/header.php';
include '../config/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Trip - Travelista</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/view_trip.css">
    <link rel="icon" href="../assets/images/logo.png">

</head>
<body>
    <div class="trip-container">
        <div class="trip-header">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1>Trip Details</h1>
        </div>

        <div class="trip-content">
            <div class="destination-section">
                <div class="destination-image">
                    <img id="destination-img" src="" alt="Destination">
                </div>
                <div class="destination-info">
                    <h2 id="destination-name"></h2>
                    <p id="destination-desc"></p>
                </div>
            </div>

            <div class="trip-details-grid">
                <div class="hotel-section">
                    <h3><i class="fas fa-hotel"></i> Hotel Information</h3>
                    <div class="hotel-content">
                        <div class="hotel-image">
                            <img id="hotel-img" src="" alt="Hotel">
                        </div>
                        <div class="hotel-info">
                            <h4 id="hotel-name"></h4>
                            <p class="hotel-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <span id="hotel-location"></span>
                            </p>
                            <div class="hotel-rating" id="hotel-rating"></div>
                        </div>
                    </div>
                </div>

                <div class="travel-section">
                    <h3><i class="fas fa-plane"></i> Travel Information</h3>
                    <div class="travel-info">
                        <div class="info-item">
                            <span class="label">Travel Type:</span>
                            <span id="travel-type"></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Departure Date:</span>
                            <span id="departure-date"></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Return Date:</span>
                            <span id="return-date"></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Total Passengers:</span>
                            <span id="total-passengers"></span>
                        </div>
                    </div>
                </div>

                <div class="price-section">
                    <h3><i class="fas fa-dollar-sign"></i> Price Details</h3>
                    <div class="price-info">
                        <div class="price-item">
                            <span class="label">Hotel Price:</span>
                            <span id="hotel-price"></span>
                        </div>
                        <div class="price-item">
                            <span class="label">Travel Package:</span>
                            <span id="travel-price"></span>
                        </div>
                        <div class="price-item total">
                            <span class="label">Total Price:</span>
                            <span id="total-price"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="#" id="edit-btn" class="btn edit-btn">
                    <i class="fas fa-edit"></i> Edit Trip
                </a>
                <button onclick="deleteTrip()" class="btn delete-btn">
                    <i class="fas fa-trash"></i> Delete Trip
                </button>
            </div>
        </div>
    </div>

    <script>
        // Fetch trip details when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const tripId = new URLSearchParams(window.location.search).get('id');
            fetchTripDetails(tripId);
        });

        function fetchTripDetails(tripId) {
            fetch(`../controller/view_trip_controller.php?id=${tripId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const booking = data.booking;
                        
                        // Set destination details
                        document.getElementById('destination-img').src = `data:image/jpeg;base64,${booking.destination.image}`;
                        document.getElementById('destination-name').textContent = booking.destination.name;
                        document.getElementById('destination-desc').textContent = booking.destination.description;

                        // Set hotel details
                        document.getElementById('hotel-img').src = `data:image/jpeg;base64,${booking.hotel.image}`;
                        document.getElementById('hotel-name').textContent = booking.hotel.name;
                        document.getElementById('hotel-location').textContent = booking.hotel.location;
                        
                        // Set hotel rating stars
                        const ratingHtml = Array(booking.hotel.rating).fill('<i class="fas fa-star"></i>').join('');
                        document.getElementById('hotel-rating').innerHTML = ratingHtml;

                        // Set travel details
                        document.getElementById('travel-type').textContent = booking.travel.type;
                        document.getElementById('departure-date').textContent = booking.dates.departure;
                        document.getElementById('return-date').textContent = booking.dates.return;
                        document.getElementById('total-passengers').textContent = booking.passengers;

                        // Set price details
                        document.getElementById('hotel-price').textContent = `$${booking.hotel.price.toFixed(2)}`;
                        document.getElementById('travel-price').textContent = `$${booking.travel.price.toFixed(2)}`;
                        document.getElementById('total-price').textContent = `$${booking.total_price.toFixed(2)}`;

                        // Set edit button href
                        document.getElementById('edit-btn').href = `edit_trip.php?id=${booking.id}`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading trip details');
                });
        }

        function deleteTrip() {
            if (confirm('Are you sure you want to delete this trip?')) {
                const tripId = new URLSearchParams(window.location.search).get('id');
                fetch(`../controller/delete_trip_controller.php?id=${tripId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'dashboard.php';
                        } else {
                            alert('Error deleting trip');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting trip');
                    });
            }
        }
    </script>
</body>
<?php include '../includes/footer.php'; ?>
</html> 