<?php
/**
 * Database Configuration
 * AI Conference Summit - Beginner Friendly Code
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password is empty
define('DB_NAME', 'ai_conference_db');

// Application configuration
define('SITE_URL', 'http://localhost/ai-conference-summit');
define('SITE_NAME', 'AI Conference Summit 2025');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Email configuration (configure with your SMTP details)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'noreply@aiconference.com');
define('FROM_NAME', 'AI Conference Summit');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>