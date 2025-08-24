<?php
/**
 * Homepage - AI Conference Summit
 * Beginner Friendly Code
 */

require_once 'config/database.php';
require_once 'classes/Database.php';
require_once 'classes/Event.php';
require_once 'classes/User.php';

$event = new Event();
$user = new User();

// Get upcoming events
$upcoming_events = $event->getUpcoming(6);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Conference Summit 2025 - The Future of Artificial Intelligence</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h2><i class="fas fa-robot"></i> AI Summit 2025</h2>
            </div>
            <div class="nav-links">
                <a href="index.php" class="active">Home</a>
                <a href="events.php">Events</a>
                <?php if ($user->isLoggedIn()): ?>
                    <a href="user/dashboard.php">Dashboard</a>
                    <?php if ($user->isAdmin()): ?>
                        <a href="admin/index.php">Admin</a>
                    <?php endif; ?>
                    <a href="auth/logout.php" class="btn-logout">Logout</a>
                <?php else: ?>
                    <a href="auth/login.php" class="btn-login">Login</a>
                    <a href="auth/register.php" class="btn-register">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-background">
            <img src="https://images.unsplash.com/photo-1485827404703-89b55fcc595e?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="AI Conference">
            <div class="hero-overlay"></div>
        </div>
        <div class="hero-content">
            <h1 class="hero-title">AI Conference Summit 2025</h1>
            <p class="hero-subtitle">Join the world's leading AI experts and innovators</p>
            <p class="hero-description">Discover the latest breakthroughs in artificial intelligence, machine learning, and emerging technologies that will shape our future.</p>
            <div class="hero-buttons">
                <a href="events.php" class="btn btn-primary">Explore Events</a>
                <a href="#about" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>10,000+</h3>
                    <p>Attendees Expected</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-microphone"></i>
                    <h3>150+</h3>
                    <p>Expert Speakers</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>50+</h3>
                    <p>Sessions & Workshops</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-globe"></i>
                    <h3>75+</h3>
                    <p>Countries Represented</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Section -->
    <section class="events-section" id="events">
        <div class="container">
            <div class="section-header">
                <h2>Upcoming Events</h2>
                <p>Don't miss these incredible AI conferences and workshops</p>
            </div>
            
            <?php if (count($upcoming_events) > 0): ?>
                <div class="events-grid">
                    <?php foreach ($upcoming_events as $evt): ?>
                        <div class="event-card">
                            <div class="event-image">
                                <?php if ($evt->banner): ?>
                                    <img src="uploads/events/<?php echo htmlspecialchars($evt->banner); ?>" alt="<?php echo htmlspecialchars($evt->title); ?>">
                                <?php else: ?>
                                    <img src="https://images.unsplash.com/photo-1485827404703-89b55fcc595e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="<?php echo htmlspecialchars($evt->title); ?>">
                                <?php endif; ?>
                                <div class="event-price">$<?php echo number_format($evt->price, 2); ?></div>
                            </div>
                            <div class="event-content">
                                <h3><?php echo htmlspecialchars($evt->title); ?></h3>
                                <p class="event-description"><?php echo htmlspecialchars(substr($evt->description, 0, 100)) . '...'; ?></p>
                                
                                <div class="event-details">
                                    <div class="event-detail">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('M j, Y', strtotime($evt->start_date)); ?></span>
                                    </div>
                                    <div class="event-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($evt->venue); ?></span>
                                    </div>
                                    <div class="event-detail">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo $evt->available_seats; ?> seats left</span>
                                    </div>
                                </div>
                                
                                <div class="event-actions">
                                    <a href="event-details.php?id=<?php echo $evt->id; ?>" class="btn btn-outline">View Details</a>
                                    <a href="book-event.php?id=<?php echo $evt->id; ?>" class="btn btn-primary">Book Now</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="section-footer">
                    <a href="events.php" class="btn btn-primary">View All Events</a>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Upcoming Events</h3>
                    <p>Check back soon for exciting AI conferences and workshops!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>About AI Summit 2025</h2>
                    <p>The AI Conference Summit is the premier gathering of artificial intelligence professionals, researchers, and enthusiasts from around the globe. Our mission is to accelerate AI innovation through knowledge sharing, networking, and collaboration.</p>
                    
                    <div class="features-list">
                        <div class="feature-item">
                            <i class="fas fa-brain"></i>
                            <div>
                                <h4>Cutting-Edge Research</h4>
                                <p>Latest breakthroughs in AI and machine learning</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-network-wired"></i>
                            <div>
                                <h4>Networking Opportunities</h4>
                                <p>Connect with industry leaders and peers</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-lightbulb"></i>
                            <div>
                                <h4>Hands-on Workshops</h4>
                                <p>Practical sessions and interactive learning</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="AI Conference">
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-robot"></i> AI Summit 2025</h3>
                    <p>Leading the future of artificial intelligence through innovation, collaboration, and knowledge sharing.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="events.php">Events</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#">Speakers</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Refund Policy</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-envelope"></i> info@aisummit2025.com</p>
                    <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                    <p><i class="fas fa-map-marker-alt"></i> San Francisco, CA</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 AI Conference Summit. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>