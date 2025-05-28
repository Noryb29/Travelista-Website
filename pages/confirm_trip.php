<?php
require_once '../alerts.php';
include '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    setAlert('Login Required', 'Please login to book a trip', 'warning', [
        'then' => 'window.location.href = "../auth/login.php"'
    ]);
    showAlertIfExists();
    exit();
}

// Get trip details from session or database
$trip_details = $_SESSION['trip_details'] ?? null;
if (!$trip_details) {
    setAlert('No Trip Selected', 'Please select a trip first', 'error', [
        'then' => 'window.location.href = "destinations.php"'
    ]);
    showAlertIfExists();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Trip - Travelista</title>
    <link rel="stylesheet" href="../assets/css/confirm_trip.css">
    <link rel="icon" href="../assets/images/logo.png">

</head>
<body>
    <div class="confirm-trip-container">
        <div class="trip-summary">
            <h2>Confirm Your Trip</h2>
            <div class="trip-details">
                <div class="destination-info">
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($trip_details['destination_img']); ?>" 
                         alt="<?php echo htmlspecialchars($trip_details['destination_name']); ?>">
                    <h3><?php echo htmlspecialchars($trip_details['destination_name']); ?></h3>
                </div>
                
                <div class="booking-form">
                    <form id="confirm-trip-form">
                        <div class="form-group">
                            <label for="departure_date">Departure Date</label>
                            <input type="date" id="departure_date" name="departure_date" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="return_date">Return Date</label>
                            <input type="date" id="return_date" name="return_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="total_passengers">Number of Passengers</label>
                            <input type="number" id="total_passengers" name="total_passengers" 
                                   min="1" max="10" value="1" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="budget">Budget (USD)</label>
                            <input type="number" id="budget" name="budget" min="0" step="100" required>
                        </div>
                        
                        <div class="price-summary">
                            <div class="price-item">
                                <span>Hotel Price:</span>
                                <span>$<?php echo number_format($trip_details['hotel_price'], 2); ?></span>
                            </div>
                            <div class="price-item">
                                <span>Travel Package:</span>
                                <span>$<?php echo number_format($trip_details['travel_type_price'], 2); ?></span>
                            </div>
                            <div class="price-item total">
                                <span>Total:</span>
                                <span>$<?php echo number_format($trip_details['hotel_price'] + $trip_details['travel_type_price'], 2); ?></span>
                            </div>
                        </div>
                        
                        <button type="submit" class="confirm-btn" id="confirm-btn">
                            <i class="fas fa-check-circle"></i>
                            Confirm Booking
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and SweetAlert2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        const form = $('#confirm-trip-form');
        const totalPrice = <?php echo $trip_details['hotel_price'] + $trip_details['travel_type_price']; ?>;

        // Set min return date based on departure
        $('#departure_date').on('change', function() {
            const departureDate = new Date($(this).val());
            const nextDay = new Date(departureDate);
            nextDay.setDate(nextDay.getDate() + 1);
            $('#return_date').attr('min', nextDay.toISOString().split('T')[0]);
        });

        // Intercept confirm button
        $('#confirm-btn').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to confirm this booking?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, confirm',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    validateAndSubmit();
                }
            });
        });

        form.off('submit'); // Disable default submit

        function validateAndSubmit() {
            const departureDate = new Date($('#departure_date').val());
            const returnDate = new Date($('#return_date').val());

            if (returnDate <= departureDate) {
                showErrorAlert('Return date must be after departure date');
                return;
            }

            const budget = parseFloat($('#budget').val());
            if (budget < totalPrice) {
                Swal.fire({
                    title: 'Budget Warning',
                    text: 'Your budget is lower than the total price. Proceed?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, proceed',
                    cancelButtonText: 'No, adjust'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitForm();
                    }
                });
            } else {
                submitForm();
            }
        }

        function showErrorAlert(message) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: message
            });
        }

        function submitForm() {
            const formData = new FormData(form[0]);

            Swal.fire({
                title: 'Confirming Trip...',
                text: 'Please wait while we process your booking',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '../controller/confirm_trip_controller.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        if (data.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Trip booked successfully!',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                didClose: () => {
                                    window.location.href = "dashboard.php";
                                }
                            });
                        } else {
                            showErrorAlert(data.message || 'Error confirming trip');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        showErrorAlert('Something went wrong! Please try again later.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    showErrorAlert('Something went wrong! Please try again later.');
                }
            });
        }

        // Return date validation on change
        $('#departure_date, #return_date').on('change', function () {
            const d = $('#departure_date').val();
            const r = $('#return_date').val();
            if (d && r && new Date(r) <= new Date(d)) {
                showErrorAlert('Return date must be after departure date');
                $('#return_date').val('');
            }
        });
    });
    </script>

    <style>
    /* [Your existing CSS styles as provided earlier] */
    </style>
</body>
</html>
