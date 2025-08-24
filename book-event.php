<?php
/**
 * Event Booking Page
 * AI Conference Summit - Beginner Friendly Code
 */

require_once 'config/database.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Event.php';
require_once 'classes/Booking.php';

$user = new User();
$event_class = new Event();
$booking = new Booking();

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header('Location: auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Get event ID
$event_id = intval($_GET['id'] ?? 0);
if (!$event_id) {
    header('Location: events.php');
    exit();
}

// Get event details
$event = $event_class->getById($event_id);
if (!$event) {
    header('Location: events.php');
    exit();
}

$error = '';
$success = '';

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seats = intval($_POST['seats'] ?? 1);
    
    if ($seats < 1 || $seats > 10) {
        $error = 'Invalid number of seats (1-10 allowed)';
    } elseif ($seats > $event->available_seats) {
        $error = 'Not enough seats available. Only ' . $event->available_seats . ' seats left.';
    } else {
        $result = $booking->create($_SESSION['user_id'], $event_id, $seats);
        
        if ($result['success']) {
            // Redirect to payment page
            header('Location: api/payment.php?booking_id=' . $result['booking_id']);
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
    <title>Book Event: <?php echo htmlspecialchars($event->title); ?> - AI Conference Summit 2025</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .booking-container {
            max-width: 1000px;
            margin: 120px auto 60px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }
        
        .event-details {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .event-image {
            height: 300px;
            position: relative;
            overflow: hidden;
        }
        
        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .event-info {
            padding: 30px;
        }
        
        .booking-form {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .price-display {
            font-size: 2.5rem;
            font-weight: 800;
            color: #00d4ff;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .availability {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            padding: 15px;
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.3);
            border-radius: 10px;
            color: #28a745;
        }
        
        .availability.limited {
            background: rgba(255, 193, 7, 0.2);
            border-color: rgba(255, 193, 7, 0.3);
            color: #ffc107;
        }
        
        .availability.sold-out {
            background: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }
        
        .seats-selector {
            margin-bottom: 25px;
        }
        
        .seats-input {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-top: 10px;
        }
        
        .seats-btn {
            width: 40px;
            height: 40px;
            border: 2px solid #00d4ff;
            background: transparent;
            color: #00d4ff;
            border-radius: 8px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .seats-btn:hover:not(:disabled) {
            background: #00d4ff;
            color: #0a0a0a;
        }
        
        .seats-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .seats-display {
            font-size: 1.5rem;
            font-weight: 600;
            min-width: 50px;
            text-align: center;
        }
        
        .total-amount {
            text-align: center;
            margin-bottom: 25px;
            padding: 20px;
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 10px;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .detail-label {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .detail-value {
            color: #ffffff;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .booking-container {
                grid-template-columns: 1fr;
                gap: 30px;
                margin: 100px auto 40px;
            }
            
            .booking-form {
                position: static;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h2><a href="index.php" style="color: inherit; text-decoration: none;"><i class="fas fa-robot"></i> AI Summit 2025</a></h2>
            </div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="events.php">Events</a>
                <a href="user/dashboard.php">Dashboard</a>
                <a href="auth/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Booking Content -->
    <div class="container">
        <div class="booking-container">
            <!-- Event Details -->
            <div class="event-details">
                <div class="event-image">
                    <?php if ($event->banner): ?>
                        <img src="uploads/events/<?php echo htmlspecialchars($event->banner); ?>" alt="<?php echo htmlspecialchars($event->title); ?>">
                    <?php else: ?>
                        <img src="https://images.unsplash.com/photo-1485827404703-89b55fcc595e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="<?php echo htmlspecialchars($event->title); ?>">
                    <?php endif; ?>
                </div>
                
                <div class="event-info">
                    <h1 style="font-size: 2rem; margin-bottom: 20px; color: #ffffff;">
                        <?php echo htmlspecialchars($event->title); ?>
                    </h1>
                    
                    <div class="event-meta" style="display: grid; gap: 15px; margin-bottom: 25px;">
                        <div class="detail-item">
                            <span class="detail-label">
                                <i class="fas fa-calendar"></i> Date & Time
                            </span>
                            <span class="detail-value">
                                <?php echo date('F j, Y g:i A', strtotime($event->start_date)); ?>
                                <?php if ($event->end_date): ?>
                                    <br><small style="color: rgba(255,255,255,0.6);">
                                        Until <?php echo date('F j, Y g:i A', strtotime($event->end_date)); ?>
                                    </small>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">
                                <i class="fas fa-map-marker-alt"></i> Venue
                            </span>
                            <span class="detail-value">
                                <?php echo htmlspecialchars($event->venue); ?>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">
                                <i class="fas fa-users"></i> Capacity
                            </span>
                            <span class="detail-value">
                                <?php echo $event->capacity; ?> attendees
                            </span>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 25px;">
                        <h3 style="color: #00d4ff; margin-bottom: 15px;">About This Event</h3>
                        <p style="color: rgba(255,255,255,0.8); line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($event->description)); ?>
                        </p>
                    </div>
                    
                    <!-- Event Highlights -->
                    <div>
                        <h3 style="color: #00d4ff; margin-bottom: 15px;">What's Included</h3>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="margin-bottom: 8px; color: rgba(255,255,255,0.8);">
                                <i class="fas fa-check" style="color: #28a745; margin-right: 10px;"></i>
                                Access to all sessions and workshops
                            </li>
                            <li style="margin-bottom: 8px; color: rgba(255,255,255,0.8);">
                                <i class="fas fa-check" style="color: #28a745; margin-right: 10px;"></i>
                                Networking opportunities with industry experts
                            </li>
                            <li style="margin-bottom: 8px; color: rgba(255,255,255,0.8);">
                                <i class="fas fa-check" style="color: #28a745; margin-right: 10px;"></i>
                                Digital materials and resources
                            </li>
                            <li style="margin-bottom: 8px; color: rgba(255,255,255,0.8);">
                                <i class="fas fa-check" style="color: #28a745; margin-right: 10px;"></i>
                                Complimentary refreshments and lunch
                            </li>
                            <li style="color: rgba(255,255,255,0.8);">
                                <i class="fas fa-check" style="color: #28a745; margin-right: 10px;"></i>
                                Certificate of participation
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Booking Form -->
            <div class="booking-form">
                <h2 style="text-align: center; margin-bottom: 20px; color: #ffffff;">
                    <i class="fas fa-ticket-alt"></i> Book Your Seats
                </h2>
                
                <div class="price-display">
                    $<?php echo number_format($event->price, 2); ?>
                    <small style="display: block; font-size: 0.4em; color: rgba(255,255,255,0.6);">per seat</small>
                </div>
                
                <!-- Availability Status -->
                <?php if ($event->available_seats > 50): ?>
                    <div class="availability">
                        <i class="fas fa-check-circle" style="margin-right: 10px;"></i>
                        <?php echo $event->available_seats; ?> seats available
                    </div>
                <?php elseif ($event->available_seats > 0): ?>
                    <div class="availability limited">
                        <i class="fas fa-exclamation-triangle" style="margin-right: 10px;"></i>
                        Only <?php echo $event->available_seats; ?> seats left!
                    </div>
                <?php else: ?>
                    <div class="availability sold-out">
                        <i class="fas fa-times-circle" style="margin-right: 10px;"></i>
                        Sold Out
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($event->available_seats > 0): ?>
                    <form method="POST" action="" id="bookingForm">
                        <div class="seats-selector">
                            <label style="display: block; text-align: center; margin-bottom: 15px; color: rgba(255,255,255,0.8);">
                                Number of Seats
                            </label>
                            
                            <div class="seats-input">
                                <button type="button" class="seats-btn" id="decreaseSeats">-</button>
                                <input type="number" 
                                       name="seats" 
                                       id="seatsInput" 
                                       value="1" 
                                       min="1" 
                                       max="<?php echo min(10, $event->available_seats); ?>" 
                                       class="seats-display" 
                                       style="background: transparent; border: none; color: #ffffff; text-align: center;"
                                       readonly>
                                <button type="button" class="seats-btn" id="increaseSeats">+</button>
                            </div>
                            
                            <p style="text-align: center; font-size: 0.9rem; color: rgba(255,255,255,0.6); margin-top: 10px;">
                                Maximum 10 seats per booking
                            </p>
                        </div>
                        
                        <!-- Total Amount -->
                        <div class="total-amount">
                            <div class="detail-item">
                                <span class="detail-label">Seats:</span>
                                <span class="detail-value" id="totalSeats">1</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Price per seat:</span>
                                <span class="detail-value">$<?php echo number_format($event->price, 2); ?></span>
                            </div>
                            <div class="detail-item" style="font-size: 1.2rem; color: #00d4ff;">
                                <span class="detail-label"><strong>Total:</strong></span>
                                <span class="detail-value" id="totalAmount"><strong>$<?php echo number_format($event->price, 2); ?></strong></span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-full" style="font-size: 1.1rem; padding: 18px;">
                            <i class="fas fa-credit-card"></i> Proceed to Payment
                        </button>
                        
                        <p style="text-align: center; font-size: 0.85rem; color: rgba(255,255,255,0.6); margin-top: 15px; line-height: 1.4;">
                            <i class="fas fa-shield-alt"></i> 
                            Secure payment processing. You can cancel within 24 hours if payment is not completed.
                        </p>
                    </form>
                <?php else: ?>
                    <div style="text-align: center; margin-top: 20px;">
                        <p style="color: rgba(255,255,255,0.7); margin-bottom: 20px;">
                            This event is currently sold out. 
                        </p>
                        <a href="events.php" class="btn btn-secondary">
                            <i class="fas fa-search"></i> Find Other Events
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- Contact Info -->
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); text-align: center;">
                    <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 10px;">
                        Need help with booking?
                    </p>
                    <p style="color: rgba(255,255,255,0.8); font-size: 0.9rem;">
                        <i class="fas fa-envelope"></i> info@aisummit2025.com<br>
                        <i class="fas fa-phone"></i> +1 (555) 123-4567
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const pricePerSeat = <?php echo $event->price; ?>;
        const maxSeats = <?php echo min(10, $event->available_seats); ?>;
        
        const seatsInput = document.getElementById('seatsInput');
        const decreaseBtn = document.getElementById('decreaseSeats');
        const increaseBtn = document.getElementById('increaseSeats');
        const totalSeats = document.getElementById('totalSeats');
        const totalAmount = document.getElementById('totalAmount');
        
        function updateTotal() {
            const seats = parseInt(seatsInput.value);
            const total = seats * pricePerSeat;
            
            totalSeats.textContent = seats;
            totalAmount.innerHTML = `<strong>${total.toFixed(2)}</strong>`;
            
            // Update button states
            decreaseBtn.disabled = seats <= 1;
            increaseBtn.disabled = seats >= maxSeats;
        }
        
        decreaseBtn.addEventListener('click', function() {
            let currentSeats = parseInt(seatsInput.value);
            if (currentSeats > 1) {
                seatsInput.value = currentSeats - 1;
                updateTotal();
            }
        });
        
        increaseBtn.addEventListener('click', function() {
            let currentSeats = parseInt(seatsInput.value);
            if (currentSeats < maxSeats) {
                seatsInput.value = currentSeats + 1;
                updateTotal();
            }
        });
        
        seatsInput.addEventListener('change', function() {
            let seats = parseInt(this.value);
            if (seats < 1) seats = 1;
            if (seats > maxSeats) seats = maxSeats;
            this.value = seats;
            updateTotal();
        });
        
        // Form submission handling
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const seats = parseInt(seatsInput.value);
            
            if (seats < 1 || seats > maxSeats) {
                e.preventDefault();
                alert(`Please select between 1 and ${maxSeats} seats`);
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            // Re-enable button after 10 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 10000);
        });
        
        // Initialize total
        updateTotal();
    </script>
</body>
</html>