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
(3, '2024-05-10', '09:00:00', '10:00:00'),
(3, '2024-05-10', '10:00:00', '11:00:00'),
(3, '2024-05-10', '11:00:00', '12:00:00'),
(3, '2024-05-11', '09:00:00', '10:00:00'),
(3, '2024-05-11', '10:00:00', '11:00:00'),
(3, '2024-05-11', '11:00:00', '12:00:00'); 