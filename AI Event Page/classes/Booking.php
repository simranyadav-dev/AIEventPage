<?php
/**
 * Booking Class - Handle event bookings
 * AI Conference Summit - Beginner Friendly Code
 */

class Booking {
    private $db;
    
    public function __construct() {
        $this->db = new Database;
    }
    
    /**
     * Create new booking
     */
    public function create($user_id, $event_id, $seats_booked) {
        try {
            // Start transaction
            $this->db->beginTransaction();
            
            // Check event availability
            $event = new Event();
            $availability = $event->checkAvailability($event_id, $seats_booked);
            
            if (!$availability['available']) {
                $this->db->rollback();
                return ['success' => false, 'message' => $availability['message']];
            }
            
            // Get event details for pricing
            $eventDetails = $event->getById($event_id);
            if (!$eventDetails) {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Event not found'];
            }
            
            // Calculate total amount
            $total_amount = $eventDetails->price * $seats_booked;
            
            // Generate booking reference
            $booking_reference = $this->generateBookingReference();
            
            // Insert booking
            $this->db->query('INSERT INTO bookings (user_id, event_id, booking_reference, seats_booked, total_amount, payment_status) 
                             VALUES (:user_id, :event_id, :booking_reference, :seats_booked, :total_amount, :payment_status)');
            
            $this->db->bind(':user_id', $user_id);
            $this->db->bind(':event_id', $event_id);
            $this->db->bind(':booking_reference', $booking_reference);
            $this->db->bind(':seats_booked', $seats_booked);
            $this->db->bind(':total_amount', $total_amount);
            $this->db->bind(':payment_status', 'pending');
            
            if ($this->db->execute()) {
                $booking_id = $this->db->lastInsertId();
                $this->db->commit();
                
                return [
                    'success' => true, 
                    'message' => 'Booking created successfully',
                    'booking_id' => $booking_id,
                    'booking_reference' => $booking_reference,
                    'total_amount' => $total_amount
                ];
            } else {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to create booking'];
            }
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update booking payment status
     */
    public function updatePaymentStatus($booking_id, $status, $payment_reference = null) {
        try {
            $this->db->query('UPDATE bookings SET payment_status = :status, payment_reference = :payment_reference WHERE id = :id');
            $this->db->bind(':status', $status);
            $this->db->bind(':payment_reference', $payment_reference);
            $this->db->bind(':id', $booking_id);
            
            if ($this->db->execute()) {
                // If payment is successful, generate QR code and ticket
                if ($status === 'paid') {
                    $this->generateTicket($booking_id);
                }
                
                return ['success' => true, 'message' => 'Payment status updated'];
            } else {
                return ['success' => false, 'message' => 'Failed to update payment status'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Generate booking reference code
     */
    private function generateBookingReference() {
        $prefix = 'AIC';
        $timestamp = date('ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        return $prefix . $timestamp . $random;
    }
    
    /**
     * Generate QR code and ticket for successful booking
     */
    private function generateTicket($booking_id) {
        $booking = $this->getById($booking_id);
        if (!$booking) return false;
        
        // Generate QR code (simplified - in real project use QR code library)
        $qr_data = "Booking: " . $booking->booking_reference . " | Event: " . $booking->event_title . " | Seats: " . $booking->seats_booked;
        $qr_filename = 'qr_' . $booking->booking_reference . '.png';
        
        // For demo purposes, we'll just create a placeholder QR code
        $qr_path = UPLOAD_PATH . 'qr-codes/' . $qr_filename;
        $this->createDummyQRCode($qr_path, $qr_data);
        
        // Update booking with QR code path
        $this->db->query('UPDATE bookings SET qr_code = :qr_code WHERE id = :id');
        $this->db->bind(':qr_code', $qr_filename);
        $this->db->bind(':id', $booking_id);
        $this->db->execute();
        
        // Send email notification
        $this->sendBookingConfirmation($booking);
        
        return true;
    }
    
    /**
     * Create dummy QR code (replace with real QR code library)
     */
    private function createDummyQRCode($path, $data) {
        // Create uploads/qr-codes directory if it doesn't exist
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        
        // Create a simple placeholder image (in real project, use proper QR code library)
        $img = imagecreate(200, 200);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        
        // Draw a simple pattern
        for ($i = 0; $i < 200; $i += 20) {
            for ($j = 0; $j < 200; $j += 20) {
                if (($i + $j) % 40 == 0) {
                    imagefilledrectangle($img, $i, $j, $i + 10, $j + 10, $black);
                }
            }
        }
        
        imagepng($img, $path);
        imagedestroy($img);
    }
    
    /**
     * Send booking confirmation email
     */
    private function sendBookingConfirmation($booking) {
        $email = new Email();
        $email->sendBookingConfirmation(
            $booking->user_email,
            $booking->user_name,
            $booking
        );
    }
    
    /**
     * Get booking by ID with event and user details
     */
    public function getById($id) {
        $this->db->query('SELECT b.*, 
                         e.title as event_title, e.start_date, e.end_date, e.venue,
                         u.full_name as user_name, u.email as user_email
                         FROM bookings b
                         JOIN events e ON b.event_id = e.id
                         JOIN users u ON b.user_id = u.id
                         WHERE b.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    /**
     * Get booking by reference
     */
    public function getByReference($reference) {
        $this->db->query('SELECT b.*, 
                         e.title as event_title, e.start_date, e.end_date, e.venue,
                         u.full_name as user_name, u.email as user_email
                         FROM bookings b
                         JOIN events e ON b.event_id = e.id
                         JOIN users u ON b.user_id = u.id
                         WHERE b.booking_reference = :reference');
        $this->db->bind(':reference', $reference);
        return $this->db->single();
    }
    
    /**
     * Get user bookings
     */
    public function getUserBookings($user_id) {
        $this->db->query('SELECT b.*, 
                         e.title as event_title, e.start_date, e.end_date, e.venue, e.banner
                         FROM bookings b
                         JOIN events e ON b.event_id = e.id
                         WHERE b.user_id = :user_id
                         ORDER BY b.booking_date DESC');
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }
    
    /**
     * Get all bookings with filters (for admin)
     */
    public function getAll($filters = []) {
        $sql = 'SELECT b.*, 
                e.title as event_title, e.start_date, e.venue,
                u.full_name as user_name, u.email as user_email
                FROM bookings b
                JOIN events e ON b.event_id = e.id
                JOIN users u ON b.user_id = u.id';
        
        $whereConditions = [];
        
        if (!empty($filters['event_id'])) {
            $whereConditions[] = 'b.event_id = :event_id';
        }
        
        if (!empty($filters['payment_status'])) {
            $whereConditions[] = 'b.payment_status = :payment_status';
        }
        
        if (!empty($filters['start_date'])) {
            $whereConditions[] = 'b.booking_date >= :start_date';
        }
        
        if (!empty($filters['end_date'])) {
            $whereConditions[] = 'b.booking_date <= :end_date';
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = '(b.booking_reference LIKE :search OR u.full_name LIKE :search OR u.email LIKE :search)';
        }
        
        if (!empty($whereConditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereConditions);
        }
        
        $sql .= ' ORDER BY b.booking_date DESC';
        
        // Add pagination if needed
        if (!empty($filters['limit'])) {
            $sql .= ' LIMIT :limit';
            if (!empty($filters['offset'])) {
                $sql .= ' OFFSET :offset';
            }
        }
        
        $this->db->query($sql);
        
        // Bind parameters
        if (!empty($filters['event_id'])) {
            $this->db->bind(':event_id', $filters['event_id']);
        }
        
        if (!empty($filters['payment_status'])) {
            $this->db->bind(':payment_status', $filters['payment_status']);
        }
        
        if (!empty($filters['start_date'])) {
            $this->db->bind(':start_date', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $this->db->bind(':end_date', $filters['end_date']);
        }
        
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $this->db->bind(':search', $searchTerm);
        }
        
        if (!empty($filters['limit'])) {
            $this->db->bind(':limit', $filters['limit'], PDO::PARAM_INT);
        }
        
        if (!empty($filters['offset'])) {
            $this->db->bind(':offset', $filters['offset'], PDO::PARAM_INT);
        }
        
        return $this->db->resultSet();
    }
    
    /**
     * Get booking statistics for admin
     */
    public function getStats() {
        $this->db->query('SELECT 
            COUNT(*) as total_bookings,
            COUNT(CASE WHEN payment_status = "paid" THEN 1 END) as paid_bookings,
            COUNT(CASE WHEN payment_status = "pending" THEN 1 END) as pending_bookings,
            SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as total_revenue,
            SUM(CASE WHEN payment_status = "paid" THEN seats_booked ELSE 0 END) as total_seats_sold
            FROM bookings');
        return $this->db->single();
    }
    
    /**
     * Cancel booking
     */
    public function cancel($booking_id, $user_id = null) {
        try {
            // If user_id is provided, check if booking belongs to user
            if ($user_id) {
                $this->db->query('SELECT id FROM bookings WHERE id = :id AND user_id = :user_id');
                $this->db->bind(':id', $booking_id);
                $this->db->bind(':user_id', $user_id);
                $booking = $this->db->single();
                
                if (!$booking) {
                    return ['success' => false, 'message' => 'Booking not found or access denied'];
                }
            }
            
            // Check if booking can be cancelled (only pending bookings)
            $this->db->query('SELECT payment_status FROM bookings WHERE id = :id');
            $this->db->bind(':id', $booking_id);
            $booking = $this->db->single();
            
            if (!$booking) {
                return ['success' => false, 'message' => 'Booking not found'];
            }
            
            if ($booking->payment_status === 'paid') {
                return ['success' => false, 'message' => 'Cannot cancel paid booking. Please contact support for refund.'];
            }
            
            // Delete the booking
            $this->db->query('DELETE FROM bookings WHERE id = :id');
            $this->db->bind(':id', $booking_id);
            
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'Booking cancelled successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to cancel booking'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get revenue by month (for admin dashboard)
     */
    public function getRevenueByMonth($months = 6) {
        $this->db->query('SELECT 
            DATE_FORMAT(booking_date, "%Y-%m") as month,
            SUM(total_amount) as revenue
            FROM bookings 
            WHERE payment_status = "paid" 
            AND booking_date >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            GROUP BY DATE_FORMAT(booking_date, "%Y-%m")
            ORDER BY month DESC');
        $this->db->bind(':months', $months, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
}
?>