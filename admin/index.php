<?php
/**
 * Admin Dashboard
 * AI Conference Summit - Beginner Friendly Code
 */

require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once '../classes/Event.php';
require_once '../classes/Booking.php';

$user = new User();

// Check if user is admin
if (!$user->isLoggedIn() || !$user->isAdmin()) {
    header('Location: ../auth/login.php');
    exit();
}

$event = new Event();
$booking = new Booking();

// Get statistics
$user_stats = $user->getUserStats();
$event_stats = $event->getStats();
$booking_stats = $booking->getStats();

// Get recent bookings
$recent_bookings = $booking->getAll(['limit' => 10]);

// Get revenue data for chart
$revenue_data = $booking->getRevenueByMonth(6);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AI Conference Summit 2025</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
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
                <a href="index.php" class="active">Dashboard</a>
                <a href="events.php">Events</a>
                <a href="bookings.php">Bookings</a>
                <a href="../auth/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <h1 class="dashboard-title">
                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
            </h1>
            <p class="dashboard-subtitle">
                Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! Here's your conference overview.
            </p>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="dashboard-content">
        <div class="container">
            <!-- Statistics Cards -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-users"></i>
                        <h3>Total Users</h3>
                    </div>
                    <div class="card-value"><?php echo number_format($user_stats->total_users ?? 0); ?></div>
                    <div class="card-description">
                        <?php echo number_format($user_stats->verified_users ?? 0); ?> verified, 
                        <?php echo number_format($user_stats->recent_users ?? 0); ?> joined this month
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-calendar-alt"></i>
                        <h3>Events</h3>
                    </div>
                    <div class="card-value"><?php echo number_format($event_stats->total_events ?? 0); ?></div>
                    <div class="card-description">
                        <?php echo number_format($event_stats->active_events ?? 0); ?> active, 
                        <?php echo number_format($event_stats->upcoming_events ?? 0); ?> upcoming
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-ticket-alt"></i>
                        <h3>Bookings</h3>
                    </div>
                    <div class="card-value"><?php echo number_format($booking_stats->total_bookings ?? 0); ?></div>
                    <div class="card-description">
                        <?php echo number_format($booking_stats->paid_bookings ?? 0); ?> paid, 
                        <?php echo number_format($booking_stats->pending_bookings ?? 0); ?> pending
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-dollar-sign"></i>
                        <h3>Total Revenue</h3>
                    </div>
                    <div class="card-value">$<?php echo number_format($booking_stats->total_revenue ?? 0, 2); ?></div>
                    <div class="card-description">
                        <?php echo number_format($booking_stats->total_seats_sold ?? 0); ?> seats sold
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-chart-line"></i>
                        <h3>Capacity Utilization</h3>
                    </div>
                    <div class="card-value">
                        <?php 
                        $utilization = ($event_stats->total_capacity > 0) 
                            ? round((($booking_stats->total_seats_sold ?? 0) / $event_stats->total_capacity) * 100, 1)
                            : 0;
                        echo $utilization; 
                        ?>%
                    </div>
                    <div class="card-description">
                        of total event capacity
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <i class="fas fa-clock"></i>
                        <h3>Quick Actions</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                        <a href="events.php?action=create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Event
                        </a>
                        <a href="bookings.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> View Bookings
                        </a>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 40px;">
                <!-- Revenue Chart -->
                <div class="dashboard-card" style="padding: 30px;">
                    <h3 style="color: #ffffff; margin-bottom: 25px; text-align: center;">
                        <i class="fas fa-chart-area"></i> Revenue Trend (Last 6 Months)
                    </h3>
                    <canvas id="revenueChart" height="300"></canvas>
                </div>

                <!-- Recent Activity -->
                <div class="dashboard-card" style="padding: 30px;">
                    <h3 style="color: #ffffff; margin-bottom: 25px; text-align: center;">
                        <i class="fas fa-clock"></i> Recent Activity
                    </h3>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php if (count($recent_bookings) > 0): ?>
                            <?php foreach ($recent_bookings as $recent): ?>
                                <div style="padding: 15px 0; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 15px;">
                                    <div style="background: rgba(0,212,255,0.2); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-ticket-alt" style="color: #00d4ff; font-size: 0.9rem;"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="color: #ffffff; font-weight: 500; font-size: 0.9rem;">
                                            <?php echo htmlspecialchars($recent->user_name); ?>
                                        </div>
                                        <div style="color: rgba(255,255,255,0.7); font-size: 0.8rem;">
                                            Booked <?php echo $recent->seats_booked; ?> seat(s)
                                        </div>
                                        <div style="color: rgba(255,255,255,0.5); font-size: 0.75rem;">
                                            <?php echo date('M j, g:i A', strtotime($recent->booking_date)); ?>
                                        </div>
                                    </div>
                                    <div class="badge badge-<?php echo $recent->payment_status === 'paid' ? 'success' : ($recent->payment_status === 'pending' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($recent->payment_status); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; color: rgba(255,255,255,0.6); padding: 40px 20px;">
                                <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 15px;"></i>
                                <p>No recent bookings</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings Table -->
            <div class="table-container" style="margin-top: 40px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 30px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <h3 style="color: #ffffff; margin: 0;">
                        <i class="fas fa-list"></i> Recent Bookings
                    </h3>
                    <a href="bookings.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                
                <?php if (count($recent_bookings) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>User</th>
                                <th>Event</th>
                                <th>Seats</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $recent): ?>
                                <tr>
                                    <td>
                                        <code><?php echo htmlspecialchars($recent->booking_reference); ?></code>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($recent->user_name); ?></div>
                                        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6);">
                                            <?php echo htmlspecialchars($recent->user_email); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($recent->event_title); ?>">
                                            <?php echo htmlspecialchars($recent->event_title); ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6);">
                                            <?php echo date('M j, Y', strtotime($recent->start_date)); ?>
                                        </div>
                                    </td>
                                    <td><?php echo $recent->seats_booked; ?></td>
                                    <td>$<?php echo number_format($recent->total_amount, 2); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $recent->payment_status === 'paid' ? 'success' : ($recent->payment_status === 'pending' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($recent->payment_status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, g:i A', strtotime($recent->booking_date)); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="bookings.php?view=<?php echo $recent->id; ?>" class="btn btn-view btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px; color: rgba(255,255,255,0.6);">
                        <i class="fas fa-ticket-alt" style="font-size: 3rem; margin-bottom: 20px;"></i>
                        <h3>No Bookings Yet</h3>
                        <p>Bookings will appear here once users start booking events.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        // Prepare revenue data
        const revenueData = <?php echo json_encode($revenue_data); ?>;
        const months = [];
        const revenues = [];
        
        // Process data for chart
        revenueData.forEach(function(item) {
            const date = new Date(item.month + '-01');
            months.push(date.toLocaleDateString('en-US', { month: 'short', year: '2-digit' }));
            revenues.push(parseFloat(item.revenue));
        });
        
        // If no data, show placeholder
        if (months.length === 0) {
            const currentDate = new Date();
            for (let i = 5; i >= 0; i--) {
                const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
                months.push(date.toLocaleDateString('en-US', { month: 'short', year: '2-digit' }));
                revenues.push(0);
            }
        }
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months.reverse(),
                datasets: [{
                    label: 'Revenue ($)',
                    data: revenues.reverse(),
                    borderColor: '#00d4ff',
                    backgroundColor: 'rgba(0, 212, 255, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#00d4ff',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.8)'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.8)',
                            callback: function(value) {
                                return '$' + value.toFixed(0);
                            }
                        },
                        beginAtZero: true
                    }
                },
                elements: {
                    point: {
                        hoverBackgroundColor: '#ff6b35'
                    }
                }
            }
        });
        
        // Auto-refresh dashboard every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 minutes
    </script>
</body>
</html>