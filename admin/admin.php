<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin_id = $_SESSION['admin_id'];
$query = "SELECT * FROM admin WHERE admin_id = :admin_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['admin_id' => $admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Travelista</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo.png" alt="Travelista" class="logo">
                <h2>Travelista</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="?section=dashboard" class="nav-item <?php echo !isset($_GET['section']) || $_GET['section'] === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="?section=users" class="nav-item <?php echo isset($_GET['section']) && $_GET['section'] === 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="?section=destinations" class="nav-item <?php echo isset($_GET['section']) && $_GET['section'] === 'destinations' ? 'active' : ''; ?>">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Destinations</span>
                </a>
                <a href="?section=hotels" class="nav-item <?php echo isset($_GET['section']) && $_GET['section'] === 'hotels' ? 'active' : ''; ?>">
                    <i class="fas fa-hotel"></i>
                    <span>Hotels</span>
                </a>
                <a href="?section=travel-types" class="nav-item <?php echo isset($_GET['section']) && $_GET['section'] === 'travel-types' ? 'active' : ''; ?>">
                    <i class="fas fa-plane"></i>
                    <span>Travel Types</span>
                </a>
                <a href="?section=bookings" class="nav-item <?php echo isset($_GET['section']) && $_GET['section'] === 'bookings' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>
                <a href="../includes/logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <div class="header-title">
                    <h1>
                        <?php
                        $section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';
                        echo ucfirst(str_replace('-', ' ', $section));
                        ?>
                    </h1>
                </div>
                <div class="admin-profile">
                    <img src="../assets/images/default-profile.jpg" alt="Admin" class="profile-img">
                    <span class="admin-name"><?php echo htmlspecialchars($admin['admin_firstname'] . ' ' . $admin['admin_lastname']); ?></span>
                </div>
            </div>

            <div class="admin-content">
                <?php if (!isset($_GET['section']) || $_GET['section'] === 'dashboard'): ?>
                    <!-- Dashboard Stats -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <i class="fas fa-users"></i>
                            <div class="stat-info">
                                <h3>Total Users</h3>
                                <p id="totalUsers">Loading...</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="stat-info">
                                <h3>Total Destinations</h3>
                                <p id="totalDestinations">Loading...</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-hotel"></i>
                            <div class="stat-info">
                                <h3>Total Hotels</h3>
                                <p id="totalHotels">Loading...</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-calendar-check"></i>
                            <div class="stat-info">
                                <h3>Total Bookings</h3>
                                <p id="totalBookings">Loading...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Bookings -->
                    <div class="recent-activity">
                        <h2>Recent Bookings</h2>
                        <div class="activity-list" id="recentBookings">
                            Loading...
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Section Content -->
                    <div class="section-header">
                        <h2>
                            <?php
                            $section = $_GET['section'];
                            echo ucfirst(str_replace('-', ' ', $section));
                            ?>
                        </h2>
                        <button class="btn-primary" onclick="showAddModal('<?php echo $section; ?>')">
                            <i class="fas fa-plus"></i>
                            Add New
                        </button>
                    </div>

                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <?php
                                    switch ($section) {
                                        case 'users':
                                            echo '<th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Actions</th>';
                                            break;
                                        case 'destinations':
                                            echo '<th>ID</th><th>Name</th><th>Description</th><th>Created At</th><th>Actions</th>';
                                            break;
                                        case 'hotels':
                                            echo '<th>ID</th><th>Name</th><th>Location</th><th>Rating</th><th>Price</th><th>Actions</th>';
                                            break;
                                        case 'travel-types':
                                            echo '<th>ID</th><th>Name</th><th>Price</th><th>Actions</th>';
                                            break;
                                        case 'bookings':
                                            echo '<th>ID</th><th>User</th><th>Destination</th><th>Hotel</th><th>Travel Type</th><th>Dates</th><th>Status</th><th>Actions</th>';
                                            break;
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                Loading...
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="modalContainer"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html> 