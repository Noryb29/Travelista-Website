<?php
require_once '../config/config.php';
session_start();

// Fetch featured destinations (latest 3)
$stmt = $pdo->query("SELECT * FROM destinations ORDER BY destination_id DESC LIMIT 3");
$featured_destinations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travelista - Your Journey Begins Here</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="../assets/images/logo.png">

</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Discover Your Next Adventure</h1>
            <p>Explore the world's most beautiful destinations with Travelista</p>
            <a href="destinations.php" class="btn btn-primary btn-lg">Start Exploring</a>
        </div>
    </section>

    <!-- Featured Destinations -->
    <section class="featured-destinations">
        <div class="container">
            <div class="section-header">
                <h2>Featured Destinations</h2>
                <p>Discover our hand-picked destinations for your next journey</p>
            </div>
            <div class="row">
                <?php foreach ($featured_destinations as $destination): ?>
                <div class="col-md-4">
                    <div class="destination-card">
                        <div class="destination-img">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($destination['destination_img']); ?>" 
                                 alt="<?php echo htmlspecialchars($destination['destination_name']); ?>">
                        </div>
                        <div class="destination-info">
                            <h3><?php echo htmlspecialchars($destination['destination_name']); ?></h3>
                            <p><?php echo htmlspecialchars($destination['destination_desc']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="destinations.php" class="btn btn-outline-primary">View All Destinations</a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-choose-us">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose Travelista</h2>
                <p>We make your travel dreams come true</p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-box">
                        <i class="fas fa-globe-americas"></i>
                        <h3>World-Class Destinations</h3>
                        <p>Carefully curated destinations that offer unique and unforgettable experiences.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <i class="fas fa-hand-holding-usd"></i>
                        <h3>Best Price Guarantee</h3>
                        <p>We ensure you get the best value for your money with our price matching policy.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <i class="fas fa-headset"></i>
                        <h3>24/7 Support</h3>
                        <p>Our dedicated support team is always ready to assist you throughout your journey.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Start Your Journey?</h2>
                <p>Join thousands of happy travelers who have explored the world with us</p>
                <div class="cta-buttons">
                    <a href="../auth/register.php" class="btn btn-primary btn-lg" style="background-color: black; color: white;">Sign Up Now</a>
                    <a href="destinations.php" class="btn btn-primary btn-lg" style="background-color: black; color: white;">Browse Destinations</a>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 