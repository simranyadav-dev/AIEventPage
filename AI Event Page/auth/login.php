<?php
/**
 * User Login Page
 * AI Conference Summit - Beginner Friendly Code
 */

require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

$user = new User();

// Redirect if already logged in
if ($user->isLoggedIn()) {
    header('Location: ../user/dashboard.php');
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $result = $user->login($username, $password);
        
        if ($result['success']) {
            // Redirect based on user role
            if ($user->isAdmin()) {
                header('Location: ../admin/index.php');
            } else {
                header('Location: ../user/dashboard.php');
            }
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AI Conference Summit 2025</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                <a href="register.php" class="btn-register">Sign Up</a>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="container">
        <div class="form-container">
            <div class="form-title">
                <i class="fas fa-sign-in-alt"></i>
                Welcome Back
            </div>
            <p style="text-align: center; color: rgba(255,255,255,0.7); margin-bottom: 30px;">
                Sign in to your AI Conference Summit account
            </p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username or Email</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           placeholder="Enter your username or email"
                           value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <div style="position: relative;">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Enter your password" 
                               required>
                        <button type="button" 
                                id="togglePassword" 
                                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: rgba(255,255,255,0.5); cursor: pointer;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="remember_me" style="margin-right: 10px;">
                        <span style="color: rgba(255,255,255,0.8);">Remember me</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="form-footer">
                <p>Don't have an account? <a href="register.php">Create one here</a></p>
                <p><a href="#" style="color: rgba(255,255,255,0.6);">Forgot your password?</a></p>
            </div>

            <!-- Demo Login Info -->
            <div style="background: rgba(255,107,53,0.1); border: 1px solid rgba(255,107,53,0.3); border-radius: 10px; padding: 20px; margin-top: 30px;">
                <h4 style="color: #ff6b35; margin-bottom: 15px;">
                    <i class="fas fa-info-circle"></i> Demo Login Credentials
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 14px;">
                    <div>
                        <strong style="color: #00d4ff;">Admin Account:</strong><br>
                        Username: <code>admin</code><br>
                        Password: <code>admin123</code>
                    </div>
                    <div>
                        <strong style="color: #00d4ff;">User Account:</strong><br>
                        <em style="color: rgba(255,255,255,0.7);">Create a new account or use admin</em>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
            submitBtn.disabled = true;
            
            // Re-enable button after 5 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });

        // Auto-focus on first empty field
        window.addEventListener('load', function() {
            const username = document.getElementById('username');
            const password = document.getElementById('password');
            
            if (!username.value) {
                username.focus();
            } else if (!password.value) {
                password.focus();
            }
        });
    </script>
</body>
</html>