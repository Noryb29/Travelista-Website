<?php
// Get the current year for the copyright notice
$current_year = date('Y');
?>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-content">
            <!-- Brand Section -->
            <div class="footer-brand">
                <div class="footer-logo">
                    <img src="../assets/images/logo.png" alt="Travelista Logo">
                    <h2>Travelista</h2>
                </div>
                <p class="footer-description">
                    Discover the world with Travelista. We help you plan the perfect trip, 
                    find amazing destinations, and create unforgettable memories.
                </p>
            </div>

            <!-- Quick Links Section -->
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li>
                        <a href="../pages/about.php">
                            <i class="fas fa-chevron-right"></i> About Us
                        </a>
                    </li>
                    <li>
                        <a href="../pages/destinations.php">
                            <i class="fas fa-chevron-right"></i> Destinations
                        </a>
                    </li>
                    <li>
                        <a href="../pages/trips.php">
                            <i class="fas fa-chevron-right"></i> Popular Trips
                        </a>
                    </li>
                    <li>
                        <a href="../pages/blog.php">
                            <i class="fas fa-chevron-right"></i> Travel Blog
                        </a>
                    </li>
                    <li>
                        <a href="../pages/contact.php">
                            <i class="fas fa-chevron-right"></i> Contact Us
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact Info Section -->
            <div class="footer-section">
                <h3>Contact Info</h3>
                <ul class="contact-info">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Canitoan, Cagayan de Oro City, Philippines</span>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        <span>09357674187</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <span>esabarabar@gmail.com</span>
                    </li>
                </ul>
            </div>

            <!-- Newsletter Section -->
            <div class="footer-section">
                <h3>Newsletter</h3>
                <p class="footer-description">
                    Subscribe to our newsletter and get exclusive deals you won't find anywhere else.
                </p>
                <form class="newsletter-form" id="newsletterForm">
                    <input type="email" class="newsletter-input" placeholder="Enter your email" required>
                    <button type="submit" class="newsletter-button">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>&copy; <?php echo $current_year; ?> Travelista. All rights reserved.</p>
            <div class="footer-bottom-links">
                <a href="/TravelistaWebsite/privacy-policy.php">Privacy Policy</a>
                <a href="/TravelistaWebsite/terms-conditions.php">Terms & Conditions</a>
                <a href="/TravelistaWebsite/faq.php">FAQ</a>
                <a href="/TravelistaWebsite/sitemap.php">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

<!-- Add Font Awesome if not already included -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- Add footer CSS -->
<link rel="stylesheet" href="/TravelistaWebsite/assets/css/footer.css">

<script>
// Newsletter form submission
document.getElementById('newsletterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('input[type="email"]').value;
    
    // Here you can add your newsletter subscription logic
    alert('Thank you for subscribing! We\'ll keep you updated with our latest news.');
    this.reset();
});
</script>
