<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Function to check if a menu item is active
function isActive($page_name) {
    global $current_page;
    return $current_page === $page_name ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Travelista</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/header.css">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
            <link rel="icon" href="../assets/images/logo.png">

        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <a href="../pages/index.php" class="brand">
                <img src="../assets/images/logo.png" alt="Travelista Logo">
                <h1 class="brand-name">Travelista</h1>
            </a>

            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>

            <nav>
                <ul class="nav-menu" id="navMenu">
<li><a href="<?php echo isset($_SESSION['user_id']) ? '../pages/dashboard.php' : '../pages/index.php'; ?>" class="nav-link <?php echo isActive('index'); ?>">Home</a></li>
<li><a href="../pages/destinations.php" class="nav-link <?php echo isActive('destinations'); ?>">Destinations</a></li>
<li><a href="../pages/hotels.php" class="nav-link <?php echo isActive('hotels'); ?>">Hotels</a></li>
<li><a href="../pages/about.php" class="nav-link <?php echo isActive('about'); ?>">About</a></li>
<li><a href="../pages/contact.php" class="nav-link <?php echo isActive('contact'); ?>">Contact</a></li>
                </ul>
            </nav>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-menu">
                    <div class="user-profile" id="userProfile">
                        <?php
                        require_once '../config/config.php';
                        $stmt = $pdo->prepare("SELECT username, firstname, lastname, profile_img FROM users WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $user = $stmt->fetch();
                        ?>
                        <div class="user-avatar">
                            <?php if (!empty($user['profile_img'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($user['profile_img']); ?>" 
                                     alt="Profile" 
                                     class="profile-img">
                            <?php else: ?>
                                <div class="profile-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <span class="user-name" style="color: black;"><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></span>
                        <i class="fas fa-chevron-down ms-2"></i>
                    </div>
                    <div class="dropdown-menu">
                        <a href="../pages/dashboard.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            Dashboard
                        </a>
                        <a href="../pages/profile.php" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            My Profile
                        </a>
                        <a href="../pages/dashboard.php" class="dropdown-item">
                            <i class="fas fa-suitcase"></i>
                            My Trips
                        </a>
                        <hr class="dropdown-divider">
                        <a href="#" onclick="handleLogout(event)" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="../auth/login.php" class="btn btn-outline">Login</a>
                    <a href="../auth/register.php" class="btn btn-outline">Sign Up</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <script>
    function toggleMobileMenu() {
        const navMenu = document.getElementById('navMenu');
        navMenu.classList.toggle('active');
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const navMenu = document.getElementById('navMenu');
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');

        if (!navMenu.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
            navMenu.classList.remove('active');
        }
    });

    // Toggle dropdown menu
    document.addEventListener('DOMContentLoaded', () => {
        const userProfile = document.getElementById('userProfile');
        const dropdownMenu = document.querySelector('.dropdown-menu');

        userProfile.addEventListener('click', (e) => {
            e.stopPropagation(); 
            dropdownMenu.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            if (!userProfile.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    });

    // Handle logout
    function handleLogout(event) {
        event.preventDefault();
        
        Swal.fire({
            title: 'Logging out...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('../controller/logout.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Goodbye!',
                        text: data.message,
                        confirmButtonColor: '#6B73FF'
                    }).then(() => {
                        window.location.href = '../auth/login.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message || 'Error logging out',
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
</script>
<!-- Add SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.user-menu {
    position: relative;
}

.user-profile {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.user-profile:hover {
    background: rgba(255, 255, 255, 0.2);
}

.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 10px;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.profile-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.user-profile:hover .profile-img {
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
    font-size: 16px;
}

.user-name {
    color: #fff;
    font-weight: 500;
    margin-right: 8px;
    font-size: 0.95rem;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    padding: 8px 0;
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.3s ease;
    z-index: 1000;
}

.dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-item {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    color: #333;
    text-decoration: none;
    transition: all 0.2s ease;
}

.dropdown-item i {
    width: 20px;
    margin-right: 10px;
    color: #6B73FF;
}

.dropdown-item:hover {
    background: #f8f9ff;
    color: #6B73FF;
}

.dropdown-divider {
    margin: 8px 0;
    border-top: 1px solid #eee;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .user-name {
        display: none;
    }
    
    .user-avatar {
        margin-right: 0;
    }
    
    .user-profile {
        padding: 6px;
    }
}
</style>
</body>
</html>
