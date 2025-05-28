<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

include '../config/config.php';

$user_id = $_SESSION['user_id'];

// Fetch user information
$user_query = "SELECT username, firstname, lastname, profile_img, email FROM users WHERE user_id = :user_id";
$user_stmt = $pdo->prepare($user_query);
$user_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$user_stmt->execute();
$user_info = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch bookings with all related information
$query = "SELECT b.booking_id, b.departure_date, b.return_date, b.total_passengers, b.budget,
                 d.destination_name, d.destination_img, d.destination_desc,
                 h.hotel_name, h.hotel_location, h.star_rating, h.hotel_img, h.price as hotel_price,
                 t.travel_type_name, t.travel_type_price
          FROM bookings_by_user b
          JOIN destinations d ON b.destination_id = d.destination_id
          JOIN hotels h ON b.hotel_id = h.hotel_id
          JOIN travel_type t ON b.travel_type_id = t.travel_type_id
          WHERE b.user_id = :user_id
          ORDER BY b.booking_id DESC";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Now include the header after all session and database operations
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Dashboard - Travelista</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="icon" href="../assets/images/logo.png">

</head>
<body>

<div class="dashboard-container">
    <div class="user-profile-section">
        <div class="user-info">
            <div class="profile-image">
                <?php if (!empty($user_info['profile_img'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($user_info['profile_img']); ?>" 
                         alt="Profile Image" 
                         class="profile-img">
                <?php else: ?>
                    <div class="profile-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="user-details">
                <h3><?php echo htmlspecialchars($user_info['firstname'] . ' ' . $user_info['lastname']); ?></h3>
                <p><?php echo htmlspecialchars($user_info['email']); ?></p>
            </div>
        </div>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <i class="fas fa-suitcase-rolling"></i>
            <h3>Total Bookings</h3>
            <p><?php echo count($bookings); ?></p>
        </div>
        <a href="create_trip.php" class="create-trip-btn">
            <i class="fas fa-plus"></i> Create New Trip
        </a>
    </div>

    <h2 class="section-title">My Booked Trips</h2>
    <div class="bookings-grid">
        <?php if (!empty($bookings)): ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-image">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($booking['destination_img']); ?>" 
                             alt="<?php echo htmlspecialchars($booking['destination_name']); ?>">
                        <div class="booking-badge">
                            <span><?php echo htmlspecialchars($booking['travel_type_name']); ?></span>
                        </div>
                        <div class="booking-actions">
                            <a href="view_trip.php?id=<?php echo $booking['booking_id']; ?>" class="action-btn view-btn" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit_trip.php?id=<?php echo $booking['booking_id']; ?>" class="action-btn edit-btn" title="Edit Trip">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteTrip(<?php echo $booking['booking_id']; ?>)" class="action-btn delete-btn" title="Delete Trip">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="booking-details">
                        <h2><?php echo htmlspecialchars($booking['destination_name']); ?></h2>
                        <div class="hotel-info">
                            <div class="hotel-header">
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($booking['hotel_img']); ?>" 
                                     alt="<?php echo htmlspecialchars($booking['hotel_name']); ?>" 
                                     class="hotel-thumbnail">
                                <div>
                                    <h3><?php echo htmlspecialchars($booking['hotel_name']); ?></h3>
                                    <div class="hotel-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($booking['hotel_location']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="hotel-rating">
                                <?php for ($i = 0; $i < (int)$booking['star_rating']; $i++): ?>
                                    <i class="fas fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="travel-info">
                            <div class="trip-dates">
                                <div class="date-item">
                                    <i class="fas fa-plane-departure"></i>
                                    <span><?php echo date('M d, Y', strtotime($booking['departure_date'])); ?></span>
                                </div>
                                <div class="date-item">
                                    <i class="fas fa-plane-arrival"></i>
                                    <span><?php echo date('M d, Y', strtotime($booking['return_date'])); ?></span>
                                </div>
                            </div>
                            <div class="price-details">
                                <div class="hotel-price">
                                    <span class="label">Hotel:</span>
                                    <span class="amount">$<?php echo number_format((float)$booking['hotel_price'], 2); ?></span>
                                </div>
                                <div class="travel-price">
                                    <span class="label">Travel Package:</span>
                                    <span class="amount">$<?php echo number_format((float)$booking['travel_type_price'], 2); ?></span>
                                </div>
                                <div class="total-price">
                                    <span class="label">Total:</span>
                                    <span class="amount">$<?php echo number_format((float)($booking['hotel_price'] + $booking['travel_type_price']), 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="no-bookings">
                <i class="fas fa-calendar-times"></i>
                <p>No bookings found. Start planning your next adventure!</p>
                <a href="create_trip.php" class="browse-btn">Create a New Trip</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteTrip(bookingId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6B73FF',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Deleting Trip...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('../controller/delete_trip_controller.php?id=' + bookingId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Your trip has been deleted.',
                            confirmButtonColor: '#6B73FF'
                        }).then(() => {
                            window.location.href = 'dashboard.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message || 'Error deleting trip',
                            confirmButtonColor: '#6B73FF'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong! Please try again later.',
                        confirmButtonColor: '#6B73FF'
                    });
                });
        }
    });
}
</script>

<style>
.user-info {
    display: flex;
    align-items: center;
    padding: 20px;
    background: linear-gradient(135deg, #fff 0%, #f8f9ff 100%);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    margin-bottom: 30px;
    border: 1px solid rgba(107, 115, 255, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.user-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(0, 0, 0, 0.08);
}

.profile-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 20px;
    border: 3px solid #fff;
    box-shadow: 0 4px 15px rgba(107, 115, 255, 0.2);
    position: relative;
}

.profile-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.profile-image:hover .profile-img {
    transform: scale(1.1);
}

.profile-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, #6B73FF, #8B5CF6);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 32px;
}

.user-details {
    flex: 1;
}

.user-details h3 {
    margin: 0;
    font-size: 1.5rem;
    color: #333;
    font-weight: 600;
    margin-bottom: 5px;
}

.user-details p {
    margin: 0;
    color: #666;
    font-size: 1rem;
    display: flex;
    align-items: center;
}

.user-details p i {
    margin-right: 8px;
    color: #6B73FF;
}

/* Add a subtle animation for the profile section */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.user-info {
    animation: fadeInUp 0.5s ease-out;
}

/* Responsive Design */
@media (max-width: 768px) {
    .user-info {
        padding: 15px;
    }

    .profile-image {
        width: 60px;
        height: 60px;
        margin-right: 15px;
    }

    .user-details h3 {
        font-size: 1.2rem;
    }

    .user-details p {
        font-size: 0.9rem;
    }
}
</style>

</body>
<?php include '../includes/footer.php'; ?>
</html>
