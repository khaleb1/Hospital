-- Drop the existing availability table if it exists
DROP TABLE IF EXISTS availability;

-- Create the availability table
CREATE TABLE availability (
    availability_id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    available_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('available', 'booked') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id) ON DELETE CASCADE,
    UNIQUE KEY unique_slot (doctor_id, available_date, start_time, end_time)
);

-- Add some sample data for testing
INSERT INTO availability (doctor_id, available_date, start_time, end_time) VALUES
-- Day 1
(3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '10:00:00'),
(3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '11:00:00'),
(3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', '12:00:00'),
(3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '15:00:00'),
(3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', '16:00:00'),
-- Day 2
(3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '10:00:00'),
(3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', '11:00:00'),
(3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', '12:00:00'),
-- Day 3
(3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', '10:00:00'),
(3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', '11:00:00'),
(3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '11:00:00', '12:00:00'); 