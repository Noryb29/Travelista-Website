<?php
require_once '../config/config.php';
session_start();

// Fetch all destinations from the database
$stmt = $pdo->prepare("SELECT * FROM destinations ORDER BY destination_id DESC");
$stmt->execute();
$destinations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinations - Travelista</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/destinations.css">
    <link rel="icon" href="../assets/images/logo.png">

</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="main-content">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="container">
                <h1>Explore Amazing Destinations</h1>
                <p>Discover the world's most breathtaking locations and plan your next adventure</p>
            </div>
        </div>

        <!-- Destinations Grid -->
        <div class="container">
            <div class="destinations-grid">
                <?php foreach ($destinations as $destination): ?>
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
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 