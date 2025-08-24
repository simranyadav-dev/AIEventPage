<?php
/**
 * Logout Handler
 * AI Conference Summit - Beginner Friendly Code
 */

require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

$user = new User();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

// Handle logout
$user->logout();

// Redirect with success message
header('Location: ../index.php?message=logout_success');
exit();
?>