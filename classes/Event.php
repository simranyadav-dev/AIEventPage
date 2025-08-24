<?php
/**
 * Event Class - Handle event management
 * AI Conference Summit - Beginner Friendly Code
 */

class Event {
    private $db;
    
    public function __construct() {
        $this->db = new Database;
    }
    
    /**
     * Create new event
     */
    public function create($data) {
        try {
            $this->db->query('INSERT INTO events (title, description, start_date, end_date, venue, capacity, price, banner, status) 
                             VALUES (:title, :description, :start_date, :end_date, :venue, :capacity, :price, :banner, :status)');
            
            $this->db->bind(':title', $data['title']);
            $this->db->bind(':description', $data['description']);
            $this->db->bind(':start_date', $data['start_date']);
            $this->db->bind(':end_date', $data['end_date']);
            $this->db->bind(':venue', $data['venue']);
            $this->db->bind(':capacity', $data['capacity']);
            $this->db->bind(':price', $data['price']);
            $this->db->bind(':banner', $data['banner'] ?? null);
            $this->db->bind(':status', $data['status'] ?? 'active');
            
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'Event created successfully', 'event_id' => $this->db->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to create event'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update event
     */
    public function update($id, $data) {
        try {
            $sql = 'UPDATE events SET title = :title, description = :description, start_date = :start_date, 
                    end_date = :end_date, venue = :venue, capacity = :capacity, price = :price, status = :status';
            
            if (isset($data['banner'])) {
                $sql .= ', banner = :banner';
            }
            
            $sql .= ' WHERE id = :id';
            
            $this->db->query($sql);
            $this->db->bind(':id', $id);
            $this->db->bind(':title', $data['title']);
            $this->db->bind(':description', $data['description']);
            $this->db->bind(':start_date', $data['start_date']);
            $this->db->bind(':end_date', $data['end_date']);
            $this->db->bind(':venue', $data['venue']);
            $this->db->bind(':capacity', $data['capacity']);
            $this->db->bind(':price', $data['price']);
            $this->db->bind(':status', $data['status']);
            
            if (isset($data['banner'])) {
                $this->db->bind(':banner', $data['banner']);
            }
            
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'Event updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update event'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Delete event
     */
    public function delete($id) {
        try {
            // Check if event has bookings
            $this->db->query('SELECT COUNT(*) as booking_count FROM bookings WHERE event_id = :id');
            $this->db->bind(':id', $id);
            $result = $this->db->single();
            
            if ($result->booking_count > 0) {
                return ['success' => false, 'message' => 'Cannot delete event with existing bookings'];
            }
            
            $this->db->query('DELETE FROM events WHERE id = :id');
            $this->db->bind(':id', $id);
            
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'Event deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete event'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get all events with filters
     */
    public function getAll($filters = []) {
        $sql = 'SELECT e.*, 
                COALESCE(SUM(b.seats_booked), 0) as booked_seats,
                (e.capacity - COALESCE(SUM(b.seats_booked), 0)) as available_seats
                FROM events e
                LEFT JOIN bookings b ON e.id = b.event_id AND b.payment_status = "paid"';
        
        $whereConditions = [];
        
        if (!empty($filters['search'])) {
            $whereConditions[] = '(e.title LIKE :search OR e.description LIKE :search OR e.venue LIKE :search)';
        }
        
        if (!empty($filters['status'])) {
            $whereConditions[] = 'e.status = :status';
        }
        
        if (!empty($filters['start_date'])) {
            $whereConditions[] = 'e.start_date >= :start_date';
        }
        
        if (!empty($filters['end_date'])) {
            $whereConditions[] = 'e.start_date <= :end_date';
        }
        
        if (!empty($whereConditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereConditions);
        }
        
        $sql .= ' GROUP BY e.id ORDER BY e.start_date ASC';
        
        $this->db->query($sql);
        
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $this->db->bind(':search', $searchTerm);
        }
        
        if (!empty($filters['status'])) {
            $this->db->bind(':status', $filters['status']);
        }
        
        if (!empty($filters['start_date'])) {
            $this->db->bind(':start_date', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $this->db->bind(':end_date', $filters['end_date']);
        }
        
        return $this->db->resultSet();
    }
    
    /**
     * Get event by ID
     */
    public function getById($id) {
        $this->db->query('SELECT e.*, 
                         COALESCE(SUM(b.seats_booked), 0) as booked_seats,
                         (e.capacity - COALESCE(SUM(b.seats_booked), 0)) as available_seats
                         FROM events e
                         LEFT JOIN bookings b ON e.id = b.event_id AND b.payment_status = "paid"
                         WHERE e.id = :id
                         GROUP BY e.id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    /**
     * Get upcoming events (for homepage)
     */
    public function getUpcoming($limit = 6) {
        $this->db->query('SELECT e.*, 
                         COALESCE(SUM(b.seats_booked), 0) as booked_seats,
                         (e.capacity - COALESCE(SUM(b.seats_booked), 0)) as available_seats
                         FROM events e
                         LEFT JOIN bookings b ON e.id = b.event_id AND b.payment_status = "paid"
                         WHERE e.status = "active" AND e.start_date > NOW()
                         GROUP BY e.id
                         ORDER BY e.start_date ASC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
    
    /**
     * Check seat availability
     */
    public function checkAvailability($event_id, $requested_seats) {
        $event = $this->getById($event_id);
        
        if (!$event) {
            return ['available' => false, 'message' => 'Event not found'];
        }
        
        if ($event->available_seats >= $requested_seats) {
            return ['available' => true, 'available_seats' => $event->available_seats];
        } else {
            return ['available' => false, 'message' => 'Not enough seats available', 'available_seats' => $event->available_seats];
        }
    }
    
    /**
     * Get event statistics for admin
     */
    public function getStats() {
        $this->db->query('SELECT 
            COUNT(*) as total_events,
            COUNT(CASE WHEN status = "active" THEN 1 END) as active_events,
            COUNT(CASE WHEN start_date > NOW() THEN 1 END) as upcoming_events,
            SUM(capacity) as total_capacity
            FROM events');
        return $this->db->single();
    }
}
?>