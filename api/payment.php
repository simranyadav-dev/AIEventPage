<?php
/**
 * Fake Payment Gateway
 * AI Conference Summit - Beginner Friendly Code
 * Simulates payment processing with success/failure scenarios
 */

require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once '../classes/Booking.php';
require_once '../classes/Email.php';

$user = new User();
$booking_class = new Booking();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Get booking ID
$booking_id = intval($_GET['booking_id'] ?? 0);
if (!$booking_id) {
    header('Location: ../user/bookings.php');
    exit();
}

// Get booking details
$booking = $booking_class->getById($booking_id);
if (!$booking || $booking->user_id != $_SESSION['user_id']) {
    header('Location: ../user/bookings.php');
    exit();
}

// Check if booking is already paid
if ($booking->payment_status === 'paid') {
    header('Location: ../user/bookings.php?success=already_paid');
    exit();
}

$error = '';
$processing = false;

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $processing = true;
    $card_number = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
    $expiry = $_POST['expiry'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $cardholder_name = trim($_POST['cardholder_name'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'card';
    
    // Validate input
    if (empty($card_number) || empty($expiry) || empty($cvv) || empty($cardholder_name)) {
        $error = 'Please fill in all payment details';
        $processing = false;
    } elseif (strlen($card_number) < 13 || strlen($card_number) > 19) {
        $error = 'Invalid card number';
        $processing = false;
    } elseif (strlen($cvv) < 3 || strlen($cvv) > 4) {
        $error = 'Invalid CVV';
        $processing = false;
    } else {
        // Simulate payment processing delay
        sleep(2);
        
        // Fake payment logic - simulate different scenarios
        $payment_success = $this->simulatePayment($card_number, $booking->total_amount);
        
        if ($payment_success['success']) {
            // Generate payment reference
            $payment_reference = 'PAY_' . strtoupper(bin2hex(random_bytes(8)));
            
            // Update booking status
            $result = $booking_class->updatePaymentStatus($booking_id, 'paid', $payment_reference);
            
            if ($result['success']) {
                // Redirect to success page
                header('Location: ../user/bookings.php?success=payment_completed&booking_ref=' . urlencode($booking->booking_reference));
                exit();
            } else {
                $error = 'Payment processed but booking update failed. Please contact support.';
            }
        } else {
            $error = $payment_success['message'];
            
            // Update booking status to failed
            $booking_class->updatePaymentStatus($booking_id, 'failed');
        }
        
        $processing = false;
    }
}

/**
 * Simulate different payment scenarios for demo purposes
 */
function simulatePayment($card_number, $amount) {
    // Test card numbers for different scenarios
    $test_cards = [
        '4111111111111111' => ['success' => true, 'message' => 'Payment successful'],     // Success
        '4000000000000002' => ['success' => false, 'message' => 'Card declined'],        // Declined
        '4000000000000119' => ['success' => false, 'message' => 'Processing error'],     // Error
        '4000000000000127' => ['success' => false, 'message' => 'Incorrect CVC'],        // CVC Error
        '4000000000000069' => ['success' => false, 'message' => 'Card expired'],         // Expired
    ];
    
    // Check for specific test cards
    if (isset($test_cards[$card_number])) {
        return $test_cards[$card_number];
    }
    
    // For other cards, simulate random success/failure (80% success rate for demo)
    $random = rand(1, 100);
    
    if ($random <= 80) {
        return ['success' => true, 'message' => 'Payment successful'];
    } elseif ($random <= 90) {
        return ['success' => false, 'message' => 'Card declined by issuer'];
    } elseif ($random <= 95) {
        return ['success' => false, 'message' => 'Insufficient funds'];
    } else {
        return ['success' => false, 'message' => 'Payment processing error'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - AI Conference Summit 2025</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 120px auto 60px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        
        .booking-summary {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            height: fit-content;
        }
        
        .payment-form {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .card-input {
            position: relative;
        }
        
        .card-input i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
        }
        
        .test-cards {
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .test-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .test-card:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .processing-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .processing-content {
            background: rgba(26, 26, 26, 0.95);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        @media (max-width: 768px) {
            .payment-container {
                grid-template-columns: 1fr;
                gap: 30px;
                margin: 100px auto 40px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
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
                <a href="../user/dashboard.php">Dashboard</a>
                <a href="../auth/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Payment Content -->
    <div class="container">
        <div class="payment-container">
            <!-- Booking Summary -->
            <div class="booking-summary">
                <h2 style="color: #ffffff; margin-bottom: 25px; text-align: center;">
                    <i class="fas fa-file-invoice"></i> Booking Summary
                </h2>
                
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #00d4ff; font-size: 1.1rem; margin-bottom: 10px;">
                        <?php echo htmlspecialchars($booking->event_title); ?>
                    </h3>
                    <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem; margin-bottom: 15px;">
                        <?php echo date('F j, Y g:i A', strtotime($booking->start_date)); ?><br>
                        <?php echo htmlspecialchars($booking->venue); ?>
                    </p>
                </div>
                
                <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                    <div class="detail-item">
                        <span class="detail-label">Booking Reference:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->booking_reference); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Seats:</span>
                        <span class="detail-value"><?php echo $booking->seats_booked; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Price per seat:</span>
                        <span class="detail-value">$<?php echo number_format($booking->total_amount / $booking->seats_booked, 2); ?></span>
                    </div>
                    <div class="detail-item" style="font-size: 1.2rem; color: #00d4ff; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1);">
                        <span class="detail-label"><strong>Total Amount:</strong></span>
                        <span class="detail-value"><strong>$<?php echo number_format($booking->total_amount, 2); ?></strong></span>
                    </div>
                </div>
                
                <div style="margin-top: 25px; padding: 15px; background: rgba(40, 167, 69, 0.2); border: 1px solid rgba(40, 167, 69, 0.3); border-radius: 10px;">
                    <p style="color: #28a745; font-size: 0.9rem; margin: 0; text-align: center;">
                        <i class="fas fa-shield-alt"></i> Secure Payment Processing
                    </p>
                </div>
            </div>
            
            <!-- Payment Form -->
            <div class="payment-form">
                <h2 style="color: #ffffff; margin-bottom: 25px; text-align: center;">
                    <i class="fas fa-credit-card"></i> Payment Details
                </h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="paymentForm">
                    <div class="form-group">
                        <label for="cardholder_name">Cardholder Name</label>
                        <input type="text" 
                               id="cardholder_name" 
                               name="cardholder_name" 
                               class="form-control" 
                               placeholder="John Doe" 
                               required
                               value="<?php echo htmlspecialchars($_POST['cardholder_name'] ?? $_SESSION['full_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <div class="card-input">
                            <input type="text" 
                                   id="card_number" 
                                   name="card_number" 
                                   class="form-control" 
                                   placeholder="1234 5678 9012 3456" 
                                   required
                                   maxlength="19"
                                   autocomplete="cc-number">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry">Expiry Date</label>
                            <input type="text" 
                                   id="expiry" 
                                   name="expiry" 
                                   class="form-control" 
                                   placeholder="MM/YY" 
                                   required
                                   maxlength="5"
                                   autocomplete="cc-exp">
                        </div>
                        
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" 
                                   id="cvv" 
                                   name="cvv" 
                                   class="form-control" 
                                   placeholder="123" 
                                   required
                                   maxlength="4"
                                   autocomplete="cc-csc">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 30px;">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" required style="margin-right: 10px;">
                            <span style="color: rgba(255,255,255,0.8);">
                                I agree to the payment terms and conditions
                            </span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-full" style="font-size: 1.1rem; padding: 18px;">
                        <i class="fas fa-lock"></i> Pay $<?php echo number_format($booking->total_amount, 2); ?>
                    </button>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="../user/bookings.php" style="color: rgba(255,255,255,0.6); text-decoration: none;">
                            <i class="fas fa-arrow-left"></i> Back to Bookings
                        </a>
                    </div>
                </form>
                
                <!-- Test Cards Information -->
                <div class="test-cards">
                    <h4 style="color: #00d4ff; margin-bottom: 15px; text-align: center;">
                        <i class="fas fa-info-circle"></i> Demo Test Cards
                    </h4>
                    <div class="test-card">
                        <code>4111111111111111</code>
                        <span style="color: #28a745; font-size: 0.8rem;">✅ Success</span>
                    </div>
                    <div class="test-card">
                        <code>4000000000000002</code>
                        <span style="color: #dc3545; font-size: 0.8rem;">❌ Declined</span>
                    </div>
                    <div class="test-card">
                        <code>4000000000000119</code>
                        <span style="color: #ffc107; font-size: 0.8rem;">⚠️ Error</span>
                    </div>
                    <p style="font-size: 0.8rem; color: rgba(255,255,255,0.6); text-align: center; margin-top: 15px; margin-bottom: 0;">
                        Use any expiry date in the future and any 3-digit CVV
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Processing Overlay -->
    <div class="processing-overlay" id="processingOverlay">
        <div class="processing-content">
            <div style="font-size: 3rem; color: #00d4ff; margin-bottom: 20px;">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            <h3 style="color: #ffffff; margin-bottom: 15px;">Processing Payment</h3>
            <p style="color: rgba(255,255,255,0.7); margin: 0;">
                Please wait while we process your payment securely...
            </p>
        </div>
    </div>

    <script>
        // Format card number input
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            
            if (value.length <= 16) {
                this.value = formattedValue;
            }
        });
        
        // Format expiry date
        document.getElementById('expiry').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            this.value = value;
        });
        
        // Only allow numbers in CVV
        document.getElementById('cvv').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Form submission
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
            const expiry = document.getElementById('expiry').value;
            const cvv = document.getElementById('cvv').value;
            const cardholderName = document.getElementById('cardholder_name').value.trim();
            
            // Basic validation
            if (cardNumber.length < 13 || cardNumber.length > 19) {
                e.preventDefault();
                alert('Please enter a valid card number');
                return false;
            }
            
            if (!/^\d{2}\/\d{2}$/.test(expiry)) {
                e.preventDefault();
                alert('Please enter expiry date in MM/YY format');
                return false;
            }
            
            if (cvv.length < 3 || cvv.length > 4) {
                e.preventDefault();
                alert('Please enter a valid CVV');
                return false;
            }
            
            if (cardholderName.length < 2) {
                e.preventDefault();
                alert('Please enter the cardholder name');
                return false;
            }
            
            // Check expiry date
            const [month, year] = expiry.split('/');
            const currentDate = new Date();
            const expiryDate = new Date(2000 + parseInt(year), parseInt(month) - 1);
            
            if (expiryDate < currentDate) {
                e.preventDefault();
                alert('Card has expired');
                return false;
            }
            
            // Show processing overlay
            document.getElementById('processingOverlay').style.display = 'flex';
            
            // Disable form
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            
            // Allow form to submit
            return true;
        });
        
        // Auto-fill test card on click
        document.querySelectorAll('.test-card code').forEach(function(element) {
            element.style.cursor = 'pointer';
            element.addEventListener('click', function() {
                document.getElementById('card_number').value = this.textContent;
                document.getElementById('expiry').value = '12/25';
                document.getElementById('cvv').value = '123';
                
                // Trigger formatting
                document.getElementById('card_number').dispatchEvent(new Event('input'));
            });
        });
        
        // Focus management
        document.getElementById('card_number').addEventListener('input', function() {
            if (this.value.replace(/\s/g, '').length === 16) {
                document.getElementById('expiry').focus();
            }
        });
        
        document.getElementById('expiry').addEventListener('input', function() {
            if (this.value.length === 5) {
                document.getElementById('cvv').focus();
            }
        });
        
        <?php if ($processing): ?>
        // Show processing overlay if payment is being processed
        document.getElementById('processingOverlay').style.display = 'flex';
        <?php endif; ?>
    </script>
</body>
</html>