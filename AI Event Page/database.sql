-- Create database
CREATE DATABASE ai_conference_db;
USE ai_conference_db;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    is_admin BOOLEAN DEFAULT FALSE,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    venue VARCHAR(200) NOT NULL,
    capacity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    banner VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    seats_booked INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_reference VARCHAR(100),
    qr_code VARCHAR(255),
    ticket_pdf VARCHAR(255),
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, is_admin, is_verified) 
VALUES ('admin', 'admin@aiconference.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', TRUE, TRUE);

-- Insert sample events
INSERT INTO events (title, description, start_date, end_date, venue, capacity, price) VALUES
('AI Revolution Summit 2025', 'The biggest AI conference of the year featuring industry leaders and breakthrough technologies.', '2025-09-15 09:00:00', '2025-09-17 18:00:00', 'San Francisco Convention Center', 500, 299.00),
('Machine Learning Expo', 'Dive deep into machine learning algorithms and real-world applications.', '2025-10-20 10:00:00', '2025-10-22 17:00:00', 'New York Tech Hub', 300, 199.00),
('Future of AI Workshop', 'Hands-on workshop exploring the future possibilities of artificial intelligence.', '2025-11-10 09:00:00', '2025-11-12 16:00:00', 'Austin Innovation Center', 150, 149.00);