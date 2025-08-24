<?php
/**
 * Email Verification Page
 * AI Conference Summit - Beginner Friendly Code
 */

require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

$user = new User();

$message = '';
$success = false;

// Get verification token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = 'Invalid verification link. Please check your email for the correct link.';
} else {
    // Verify the token
    $result = $user->verifyEmail($token);
    $success = $result['success'];
    $message = $result['message'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - AI Conference Summit 2025</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .verify-container {
            max-width: 600px;
            margin: 120px auto 60px;
            text-align: center;
        }
        
        .verify-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 60px 40px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .verify-icon {
            font-size: 4rem;
            margin-bottom: 30px;
        }
        
        .verify-icon.success {
            color: #28a745;
            animation: bounceIn 0.6s ease-out;
        }
        
        .verify-icon.error {
            color: #dc3545;
            animation: shake 0.6s ease-out;
        }
        
        .verify-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .verify-title.success {
            color: #28a745;
        }
        
        .verify-title.error {
            color: #dc3545;
        }
        
        .verify-message {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .verify-actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        @media (max-width: 768px) {
            .verify-card {
                padding: 40px 30px;
                margin: 100px 20px 40px;
            }
            
            .verify-actions {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h2><a href="../index.php" style="color: inherit; text-decoration: none;"><i class="fas fa-robot"></i> AI Summit 2025</a></h2>
            </div>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="../events.php">Events</a>
                <a href="login.php" class="btn-login">Login</a>
                <a href="register.php" class="btn-register">Sign Up</a>
            </div>
        </div>
    </nav>

    <!-- Verification Content -->
    <div class="container">
        <div class="verify-container">
            <div class="verify-card">
                <?php if ($success): ?>
                    <!-- Success State -->
                    <div class="verify-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1 class="verify-title success">Email Verified Successfully!</h1>
                    <p class="verify-message">
                        🎉 Congratulations! Your email has been verified and your account is now active. 
                        You can now sign in and start booking amazing AI conferences.
                    </p>
                    <div class="verify-actions">
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Sign In Now
                        </a>
                        <a href="../events.php" class="btn btn-secondary">
                            <i class="fas fa-calendar"></i> Browse Events
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Error State -->
                    <div class="verify-icon error">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h1 class="verify-title error">Verification Failed</h1>
                    <p class="verify-message">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                    
                    <div style="background: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.3); border-radius: 10px; padding: 20px; margin: 30px 0; text-align: left;">
                        <h4 style="color: #ffc107; margin-bottom: 15px;">
                            <i class="fas fa-lightbulb"></i> What can you do?
                        </h4>
                        <ul style="color: rgba(255, 255, 255, 0.8); margin: 0; padding-left: 20px;">
                            <li style="margin-bottom: 8px;">Check if the verification link is complete and not broken</li>
                            <li style="margin-bottom: 8px;">Make sure you're using the latest verification email</li>
                            <li style="margin-bottom: 8px;">Try copying the entire link from your email</li>
                            <li>Contact support if the problem persists</li>
                        </ul>
                    </div>
                    
                    <div class="verify-actions">
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Register Again
                        </a>
                        <a href="login.php" class="btn btn-secondary">
                            <i class="fas fa-sign-in-alt"></i> Try Login
                        </a>
                        <a href="../index.php" class="btn btn-outline">
                            <i class="fas fa-home"></i> Go Home
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- Help Section -->
                <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <h4 style="color: #00d4ff; margin-bottom: 15px;">
                        <i class="fas fa-question-circle"></i> Need Help?
                    </h4>
                    <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem; margin-bottom: 15px;">
                        If you're having trouble with verification, our support team is here to help.
                    </p>
                    <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; font-size: 0.9rem;">
                        <a href="mailto:support@aisummit2025.com" style="color: #00d4ff; text-decoration: none;">
                            <i class="fas fa-envelope"></i> support@aisummit2025.com
                        </a>
                        <a href="tel:+15551234567" style="color: #00d4ff; text-decoration: none;">
                            <i class="fas fa-phone"></i> +1 (555) 123-4567
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Additional Info -->
            <?php if ($success): ?>
                <div style="margin-top: 40px; text-align: center;">
                    <div style="background: rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.3); border-radius: 15px; padding: 25px;">
                        <h4 style="color: #00d4ff; margin-bottom: 15px;">
                            <i class="fas fa-rocket"></i> What's Next?
                        </h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                            <div>
                                <i class="fas fa-search" style="color: #00d4ff; font-size: 1.5rem; margin-bottom: 10px;"></i>
                                <h5 style="color: #ffffff; margin-bottom: 8px;">Explore Events</h5>
                                <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem; margin: 0;">
                                    Browse our curated selection of AI conferences and workshops
                                </p>
                            </div>
                            <div>
                                <i class="fas fa-ticket-alt" style="color: #00d4ff; font-size: 1.5rem; margin-bottom: 10px;"></i>
                                <h5 style="color: #ffffff; margin-bottom: 8px;">Book Tickets</h5>
                                <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem; margin: 0;">
                                    Secure your seats at cutting-edge AI events worldwide
                                </p>
                            </div>
                            <div>
                                <i class="fas fa-users" style="color: #00d4ff; font-size: 1.5rem; margin-bottom: 10px;"></i>
                                <h5 style="color: #ffffff; margin-bottom: 8px;">Network</h5>
                                <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem; margin: 0;">
                                    Connect with AI professionals and industry leaders
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Auto-redirect to login after successful verification
        <?php if ($success): ?>
        setTimeout(function() {
            if (confirm('Would you like to sign in now?')) {
                window.location.href = 'login.php';
            }
        }, 3000);
        <?php endif; ?>
        
        // Add some interactive feedback
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>