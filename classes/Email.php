<?php
/**
 * Email Class - Handle email notifications
 * AI Conference Summit - Beginner Friendly Code
 */

class Email {
    
    /**
     * Send verification email
     */
    public function sendVerificationEmail($to_email, $user_name, $verification_link) {
        $subject = "Verify Your AI Conference Summit Account";
        $message = $this->getVerificationEmailTemplate($user_name, $verification_link);
        
        return $this->sendEmail($to_email, $subject, $message);
    }
    
    /**
     * Send booking confirmation email
     */
    public function sendBookingConfirmation($to_email, $user_name, $booking) {
        $subject = "Booking Confirmation - " . $booking->event_title;
        $message = $this->getBookingConfirmationTemplate($user_name, $booking);
        
        return $this->sendEmail($to_email, $subject, $message);
    }
    
    /**
     * Send payment success email with ticket
     */
    public function sendPaymentSuccess($to_email, $user_name, $booking) {
        $subject = "Payment Successful - Your Ticket is Ready!";
        $message = $this->getPaymentSuccessTemplate($user_name, $booking);
        
        return $this->sendEmail($to_email, $subject, $message);
    }
    
    /**
     * Send actual email using PHP mail() function
     * In production, use proper SMTP library like PHPMailer or SwiftMailer
     */
    private function sendEmail($to, $subject, $message) {
        // Email headers
        $headers = array(
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . FROM_NAME . ' <' . FROM_EMAIL . '>',
            'Reply-To: ' . FROM_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        );
        
        // For development/demo purposes, we'll log emails instead of sending
        // In production, replace this with actual email sending
        $this->logEmail($to, $subject, $message);
        
        // Uncomment below line for actual email sending
        // return mail($to, $subject, $message, implode("\r\n", $headers));
        
        return true; // Return true for demo purposes
    }
    
    /**
     * Log email for development purposes
     */
    private function logEmail($to, $subject, $message) {
        $log_dir = __DIR__ . '/../uploads/email_logs/';
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        
        $log_file = $log_dir . 'emails_' . date('Y-m-d') . '.log';
        $log_entry = "[" . date('Y-m-d H:i:s') . "] TO: $to | SUBJECT: $subject\n";
        $log_entry .= "MESSAGE:\n$message\n";
        $log_entry .= str_repeat("-", 50) . "\n\n";
        
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Email verification template
     */
    private function getVerificationEmailTemplate($user_name, $verification_link) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Verify Your Account</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #00d4ff, #ff6b35); padding: 30px; text-align: center; color: white; }
                .content { background: #f9f9f9; padding: 30px; }
                .button { display: inline-block; background: #00d4ff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🤖 AI Conference Summit 2025</h1>
                    <h2>Welcome to the Future of AI!</h2>
                </div>
                <div class="content">
                    <h3>Hello ' . htmlspecialchars($user_name) . ',</h3>
                    <p>Thank you for joining the AI Conference Summit 2025! We\'re excited to have you be part of this revolutionary event.</p>
                    <p>To complete your registration and start booking amazing AI conferences, please verify your email address by clicking the button below:</p>
                    <div style="text-align: center;">
                        <a href="' . $verification_link . '" class="button">Verify My Account</a>
                    </div>
                    <p>Or copy and paste this link in your browser:</p>
                    <p style="word-break: break-all; background: #eeeeee; padding: 10px; border-radius: 4px;">' . $verification_link . '</p>
                    <p><strong>This verification link will expire in 24 hours.</strong></p>
                    <p>If you didn\'t create this account, please ignore this email.</p>
                    <p>Best regards,<br>The AI Conference Summit Team</p>
                </div>
                <div class="footer">
                    <p>&copy; 2025 AI Conference Summit. All rights reserved.</p>
                    <p>San Francisco, CA | info@aisummit2025.com</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Booking confirmation template
     */
    private function getBookingConfirmationTemplate($user_name, $booking) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Booking Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #00d4ff, #ff6b35); padding: 30px; text-align: center; color: white; }
                .content { background: #f9f9f9; padding: 30px; }
                .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #00d4ff; }
                .detail-row { display: flex; justify-content: space-between; margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #eee; }
                .detail-label { font-weight: bold; color: #555; }
                .detail-value { color: #333; }
                .button { display: inline-block; background: #00d4ff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎯 Booking Confirmed!</h1>
                    <h2>AI Conference Summit 2025</h2>
                </div>
                <div class="content">
                    <h3>Hello ' . htmlspecialchars($user_name) . ',</h3>
                    <p>Great news! Your booking has been confirmed. Here are your booking details:</p>
                    
                    <div class="booking-details">
                        <h4 style="margin-top: 0; color: #00d4ff;">📋 Booking Information</h4>
                        <div class="detail-row">
                            <span class="detail-label">Booking Reference:</span>
                            <span class="detail-value"><strong>' . htmlspecialchars($booking->booking_reference) . '</strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Event:</span>
                            <span class="detail-value">' . htmlspecialchars($booking->event_title) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date:</span>
                            <span class="detail-value">' . date('F j, Y g:i A', strtotime($booking->start_date)) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Venue:</span>
                            <span class="detail-value">' . htmlspecialchars($booking->venue) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Seats Booked:</span>
                            <span class="detail-value">' . $booking->seats_booked . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Amount:</span>
                            <span class="detail-value"><strong> . number_format($booking->total_amount, 2) . '</strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Status:</span>
                            <span class="detail-value">' . ucfirst($booking->payment_status) . '</span>
                        </div>
                    </div>
                    
                    <div class="warning">
                        <strong>⏰ Payment Required:</strong> Your booking is confirmed but payment is still pending. Please complete your payment to secure your seats.
                    </div>
                    
                    <div style="text-align: center;">
                        <a href="' . SITE_URL . '/user/bookings.php" class="button">View My Bookings</a>
                    </div>
                    
                    <p><strong>Important Notes:</strong></p>
                    <ul>
                        <li>Keep your booking reference safe - you\'ll need it for check-in</li>
                        <li>Complete payment within 24 hours to secure your booking</li>
                        <li>Your tickets will be emailed once payment is confirmed</li>
                        <li>Check-in opens 1 hour before the event starts</li>
                    </ul>
                    
                    <p>Questions? Contact us at info@aisummit2025.com or call +1 (555) 123-4567</p>
                    
                    <p>Best regards,<br>The AI Conference Summit Team</p>
                </div>
                <div class="footer">
                    <p>&copy; 2025 AI Conference Summit. All rights reserved.</p>
                    <p>San Francisco, CA | info@aisummit2025.com</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Payment success template
     */
    private function getPaymentSuccessTemplate($user_name, $booking) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Payment Successful - Ticket Ready!</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #28a745, #20c997); padding: 30px; text-align: center; color: white; }
                .content { background: #f9f9f9; padding: 30px; }
                .ticket-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745; }
                .detail-row { display: flex; justify-content: space-between; margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #eee; }
                .detail-label { font-weight: bold; color: #555; }
                .detail-value { color: #333; }
                .button { display: inline-block; background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
                .success-badge { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 8px; margin: 15px 0; text-align: center; }
                .qr-section { text-align: center; background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎉 Payment Successful!</h1>
                    <h2>Your Ticket is Ready</h2>
                </div>
                <div class="content">
                    <div class="success-badge">
                        <h3 style="margin: 0;">✅ Payment Confirmed - You\'re All Set!</h3>
                    </div>
                    
                    <h3>Hello ' . htmlspecialchars($user_name) . ',</h3>
                    <p>Congratulations! Your payment has been processed successfully. Your tickets for the AI Conference Summit 2025 are now ready!</p>
                    
                    <div class="ticket-info">
                        <h4 style="margin-top: 0; color: #28a745;">🎫 Your Ticket Details</h4>
                        <div class="detail-row">
                            <span class="detail-label">Booking Reference:</span>
                            <span class="detail-value"><strong>' . htmlspecialchars($booking->booking_reference) . '</strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Event:</span>
                            <span class="detail-value">' . htmlspecialchars($booking->event_title) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date & Time:</span>
                            <span class="detail-value">' . date('F j, Y g:i A', strtotime($booking->start_date)) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Venue:</span>
                            <span class="detail-value">' . htmlspecialchars($booking->venue) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Seats:</span>
                            <span class="detail-value">' . $booking->seats_booked . ' seat(s)</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Paid:</span>
                            <span class="detail-value"><strong> . number_format($booking->total_amount, 2) . '</strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Reference:</span>
                            <span class="detail-value">' . htmlspecialchars($booking->payment_reference) . '</span>
                        </div>
                    </div>
                    
                    ' . ($booking->qr_code ? '
                    <div class="qr-section">
                        <h4 style="color: #28a745;">📱 Your QR Code Ticket</h4>
                        <p>Show this QR code at the venue for quick check-in:</p>
                        <img src="' . SITE_URL . '/uploads/qr-codes/' . $booking->qr_code . '" alt="QR Code Ticket" style="max-width: 200px;">
                        <p><small>Save this email or screenshot the QR code for easy access</small></p>
                    </div>' : '') . '
                    
                    <div style="text-align: center;">
                        <a href="' . SITE_URL . '/user/bookings.php" class="button">View My Tickets</a>
                        <a href="' . SITE_URL . '/events.php" class="button" style="background: #00d4ff;">Book More Events</a>
                    </div>
                    
                    <p><strong>📋 What to Expect:</strong></p>
                    <ul>
                        <li><strong>Check-in:</strong> Doors open 1 hour before the event</li>
                        <li><strong>What to bring:</strong> Just your QR code ticket (digital or printed)</li>
                        <li><strong>Networking:</strong> Connect with industry leaders and innovators</li>
                        <li><strong>Materials:</strong> All session materials will be available digitally</li>
                        <li><strong>Refreshments:</strong> Complimentary coffee, lunch, and networking snacks</li>
                    </ul>
                    
                    <p><strong>🎯 Get Ready for an Amazing Experience!</strong></p>
                    <p>We\'re excited to see you at the AI Conference Summit 2025. This is going to be an incredible event with cutting-edge insights, networking opportunities, and hands-on workshops.</p>
                    
                    <p>Need help? Contact us:</p>
                    <ul>
                        <li>📧 Email: info@aisummit2025.com</li>
                        <li>📞 Phone: +1 (555) 123-4567</li>
                        <li>💬 Live Chat: Available on our website</li>
                    </ul>
                    
                    <p>See you at the summit!</p>
                    <p>Best regards,<br>The AI Conference Summit Team</p>
                </div>
                <div class="footer">
                    <p>&copy; 2025 AI Conference Summit. All rights reserved.</p>
                    <p>San Francisco, CA | info@aisummit2025.com</p>
                </div>
            </div>
        </body>
        </html>';
    }
}
?>