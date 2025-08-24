<?php
/**
 * User Registration Page
 * AI Conference Summit - Beginner Friendly Code
 */

require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once '../classes/Email.php';

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
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $terms = isset($_POST['terms']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters long';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username can only contain letters, numbers, and underscores';
    } elseif (!$terms) {
        $error = 'Please accept the terms and conditions';
    } else {
        $data = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'full_name' => $full_name,
            'phone' => $phone
        ];
        
        $result = $user->register($data);
        
        if ($result['success']) {
            $success = $result['message'];
            // Clear form data on success
            $username = $email = $full_name = $phone = '';
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
    <title>Sign Up - AI Conference Summit 2025</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 600px;
            margin: 120px auto 60px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
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
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <div class="container">
        <div class="form-container">
            <div class="form-title">
                <i class="fas fa-user-plus"></i>
                Join AI Summit 2025
            </div>
            <p style="text-align: center; color: rgba(255,255,255,0.7); margin-bottom: 30px;">
                Create your account and start exploring cutting-edge AI events
            </p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    <div style="margin-top: 15px;">
                        <a href="login.php" class="btn btn-primary">Go to Login</a>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Username *</label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="form-control" 
                               placeholder="Choose a username"
                               value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                               required
                               minlength="3"
                               pattern="[a-zA-Z0-9_]+"
                               title="Username can only contain letters, numbers, and underscores">
                        <div id="usernameHelp" style="font-size: 12px; color: rgba(255,255,255,0.6); margin-top: 5px;">
                            3+ characters, letters, numbers, and underscores only
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email Address *</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="Enter your email"
                               value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="full_name"><i class="fas fa-id-card"></i> Full Name *</label>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           class="form-control" 
                           placeholder="Enter your full name"
                           value="<?php echo htmlspecialchars($full_name ?? ''); ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           class="form-control" 
                           placeholder="Enter your phone number (optional)"
                           value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password *</label>
                        <div style="position: relative;">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="Create a strong password" 
                                   required
                                   minlength="6">
                            <button type="button" 
                                    id="togglePassword" 
                                    style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: rgba(255,255,255,0.5); cursor: pointer;">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="passwordStrength" class="password-strength"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password *</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-control" 
                               placeholder="Confirm your password" 
                               required
                               minlength="6">
                        <div id="passwordMatch" style="font-size: 12px; margin-top: 5px;"></div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label style="display: flex; align-items: flex-start; cursor: pointer;">
                        <input type="checkbox" name="terms" required style="margin-right: 10px; margin-top: 5px;">
                        <span style="color: rgba(255,255,255,0.8); line-height: 1.5;">
                            I agree to the <a href="#" style="color: #00d4ff;">Terms and Conditions</a> and 
                            <a href="#" style="color: #00d4ff;">Privacy Policy</a> of AI Conference Summit 2025
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>

            <!-- Features Preview -->
            <div style="background: rgba(0,212,255,0.1); border: 1px solid rgba(0,212,255,0.3); border-radius: 10px; padding: 20px; margin-top: 30px;">
                <h4 style="color: #00d4ff; margin-bottom: 15px;">
                    <i class="fas fa-star"></i> What you'll get:
                </h4>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #28a745; margin-right: 10px;"></i> Access to exclusive AI conferences</li>
                    <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #28a745; margin-right: 10px;"></i> Digital tickets with QR codes</li>
                    <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #28a745; margin-right: 10px;"></i> Booking management dashboard</li>
                    <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #28a745; margin-right: 10px;"></i> Email notifications and updates</li>
                    <li><i class="fas fa-check" style="color: #28a745; margin-right: 10px;"></i> Networking with AI professionals</li>
                </ul>
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

        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            let strength = 0;
            let feedback = '';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            switch(strength) {
                case 0:
                case 1:
                    feedback = '<span class="strength-weak">⚡ Weak password</span>';
                    break;
                case 2:
                case 3:
                    feedback = '<span class="strength-medium">🔸 Medium strength</span>';
                    break;
                case 4:
                case 5:
                    feedback = '<span class="strength-strong">✅ Strong password</span>';
                    break;
            }
            
            strengthDiv.innerHTML = feedback;
        });

        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    matchDiv.innerHTML = '<span style="color: #28a745;">✅ Passwords match</span>';
                } else {
                    matchDiv.innerHTML = '<span style="color: #dc3545;">❌ Passwords do not match</span>';
                }
            } else {
                matchDiv.innerHTML = '';
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.querySelector('input[name="terms"]').checked;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
            
            if (!terms) {
                e.preventDefault();
                alert('Please accept the terms and conditions');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
            submitBtn.disabled = true;
            
            // Re-enable button after 10 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 10000);
        });

        // Real-time username validation
        document.getElementById('username').addEventListener('input', function() {
            const username = this.value;
            const helpDiv = document.getElementById('usernameHelp');
            
            if (username.length > 0) {
                if (username.length < 3) {
                    helpDiv.innerHTML = '<span style="color: #dc3545;">Too short (minimum 3 characters)</span>';
                } else if (!username.match(/^[a-zA-Z0-9_]+$/)) {
                    helpDiv.innerHTML = '<span style="color: #dc3545;">Invalid characters (only letters, numbers, _)</span>';
                } else {
                    helpDiv.innerHTML = '<span style="color: #28a745;">✅ Valid username</span>';
                }
            } else {
                helpDiv.innerHTML = '3+ characters, letters, numbers, and underscores only';
            }
        });
    </script>
</body>
</html>