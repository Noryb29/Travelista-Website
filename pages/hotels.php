<?php
require_once '../config/config.php';
session_start();

// Fetch all hotels from the database
$stmt = $pdo->prepare("SELECT * FROM hotels ORDER BY hotel_id DESC");
$stmt->execute();
$hotels = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotels - Travelista</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS (reuse destinations.css for design match) -->
    <link rel="stylesheet" href="../assets/css/destinations.css">
    <link rel="icon" href="../assets/images/logo.png">

</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="main-content">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="container">
                <h1>Find Your Perfect Hotel</h1>
                <p>Browse our curated list of hotels and book your stay for a memorable trip</p>
            </div>
        </div>

        <!-- Hotels Grid -->
        <div class="container">
            <div class="destinations-grid">
                <?php foreach ($hotels as $hotel): ?>
                <div class="destination-card">
                    <div class="destination-img">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($hotel['hotel_img']); ?>" 
                             alt="<?php echo htmlspecialchars($hotel['hotel_name']); ?>">
                    </div>
                    <div class="destination-info">
                        <h3><?php echo htmlspecialchars($hotel['hotel_name']); ?></h3>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['hotel_location']); ?></p>
                        <div class="hotel-rating">
                            <?php for ($i = 0; $i < (int)$hotel['star_rating']; $i++): ?>
                                <i class="fas fa-star" style="color: #f7b500;"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="hotel-price mt-2">
                            <span class="badge bg-primary">$<?php echo number_format((float)$hotel['price'], 2); ?>/night</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
