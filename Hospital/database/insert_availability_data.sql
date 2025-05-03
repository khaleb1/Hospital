-- First, clear any existing data for doctor_id 3
DELETE FROM availability WHERE doctor_id = 3;

-- Insert new availability data for the next 7 days
INSERT INTO availability (doctor_id, available_date, available_time, status) VALUES
-- Day 1
(3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', 'available'),
-- Day 2
(3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', 'available'),
-- Day 3
(3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '11:00:00', 'available'),
-- Day 4
(3, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '09:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '11:00:00', 'available'),
-- Day 5
(3, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '09:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '10:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '11:00:00', 'available'),
-- Day 6
(3, DATE_ADD(CURDATE(), INTERVAL 6 DAY), '09:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 6 DAY), '10:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 6 DAY), '11:00:00', 'available'),
-- Day 7
(3, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '09:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '10:00:00', 'available'),
(3, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '11:00:00', 'available'); 