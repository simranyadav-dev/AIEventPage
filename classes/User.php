<?php
/**
 * User Class - Handle user authentication and management
 * AI Conference Summit - Beginner Friendly Code
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database;
    }
    
    /**
     * Register new user
     */
    public function register($data) {
        try {
            // Check if user already exists
            $this->db->query('SELECT id FROM users WHERE email = :email OR username = :username');
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':username', $data['username']);
            $this->db->execute();
            
            if ($this->db->rowCount() > 0) {
                return ['success' => false, 'message' => 'User already exists with this email or username'];
            }
            
            // Generate verification token
            $verification_token = bin2hex(random_bytes(32));
            
            // Insert user
            $this->db->query('INSERT INTO users (username, email, password, full_name, phone, verification_token) 
                             VALUES (:username, :email, :password, :full_name, :phone, :verification_token)');
            
            $this->db->bind(':username', $data['username']);
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
            $this->db->bind(':full_name', $data['full_name']);
            $this->db->bind(':phone', $data['phone']);
            $this->db->bind(':verification_token', $verification_token);
            
            if ($this->db->execute()) {
                $user_id = $this->db->lastInsertId();
                
                // Send verification email
                $email = new Email();
                $verification_link = SITE_URL . "/auth/verify.php?token=" . $verification_token;
                $email->sendVerificationEmail($data['email'], $data['full_name'], $verification_link);
                
                return [
                    'success' => true, 
                    'message' => 'Registration successful! Please check your email to verify your account.',
                    'user_id' => $user_id
                ];
            } else {
                return ['success' => false, 'message' => 'Registration failed. Please try again.'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Login user
     */
    public function login($username, $password) {
        try {
            $this->db->query('SELECT * FROM users WHERE (username = :username OR email = :username)');
            $this->db->bind(':username', $username);
            $user = $this->db->single();
            
            if ($user) {
                if (password_verify($password, $user->password)) {
                    if (!$user->is_verified) {
                        return ['success' => false, 'message' => 'Please verify your email first'];
                    }
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['username'] = $user->username;
                    $_SESSION['full_name'] = $user->full_name;
                    $_SESSION['is_admin'] = $user->is_admin;
                    $_SESSION['logged_in'] = true;
                    
                    return ['success' => true, 'message' => 'Login successful', 'user' => $user];
                } else {
                    return ['success' => false, 'message' => 'Invalid password'];
                }
            } else {
                return ['success' => false, 'message' => 'User not found'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Verify email token
     */
    public function verifyEmail($token) {
        try {
            $this->db->query('SELECT id FROM users WHERE verification_token = :token');
            $this->db->bind(':token', $token);
            $user = $this->db->single();
            
            if ($user) {
                $this->db->query('UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = :id');
                $this->db->bind(':id', $user->id);
                $this->db->execute();
                
                return ['success' => true, 'message' => 'Email verified successfully! You can now login.'];
            } else {
                return ['success' => false, 'message' => 'Invalid verification token'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        return true;
    }
    
    /**
     * Get user statistics for admin
     */
    public function getUserStats() {
        $this->db->query('SELECT 
            COUNT(*) as total_users,
            COUNT(CASE WHEN is_verified = 1 THEN 1 END) as verified_users,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_users
            FROM users WHERE is_admin = 0');
        return $this->db->single();
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($user_id, $data) {
        try {
            $this->db->query('UPDATE users SET full_name = :full_name, phone = :phone WHERE id = :id');
            $this->db->bind(':full_name', $data['full_name']);
            $this->db->bind(':phone', $data['phone']);
            $this->db->bind(':id', $user_id);
            
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'Profile updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update profile'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>