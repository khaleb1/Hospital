<?php
// Ensure error reporting is enabled for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php'; // Ensure this path is correct and file exists
require_once '../includes/db.php';   // Ensure this path is correct and file exists

header('Content-Type: application/json');

// Check if DB connection was successful
if (!$conn) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database connection failed. Check includes/db.php']);
    exit();
}

// --- Doctor ID Check (Mandatory) ---
if (!isset($_GET['doctor_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Doctor ID not provided']);
    exit();
}
if (!is_numeric($_GET['doctor_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid Doctor ID format']);
    exit();
}
$doctorId = (int)$_GET['doctor_id'];

// First, let's check if the table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'doctor_availability'");
if ($tableCheck->num_rows === 0) {
    http_response_code(500);
    echo json_encode(['error' => 'Doctor availability table does not exist']);
    exit();
}

// Let's check if there's any data for this doctor
$dataCheck = $conn->query("SELECT COUNT(*) as count FROM doctor_availability WHERE doctor_id = $doctorId");
$row = $dataCheck->fetch_assoc();

// If no data found for this doctor, return empty array
if ($row['count'] == 0) {
    echo json_encode([]);
    exit();
}

// --- Available Date Parameter (Optional) ---
if (isset($_GET['available_date'])) {
    // If a specific date is requested, return time slots for that date
    $date = $conn->real_escape_string($_GET['available_date']);
    
    $query = "SELECT 
                availability_id as id,
                start_time as start, 
                end_time as end
              FROM doctor_availability 
              WHERE doctor_id = $doctorId 
              AND available_date = '$date'
              AND available_date >= CURDATE()
              ORDER BY start_time";
    
    $result = $conn->query($query);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
        exit();
    }
    
    $slots = [];
    while ($row = $result->fetch_assoc()) {
        // Format the times to include AM/PM
        $startTime = date('h:i A', strtotime($row['start']));
        $endTime = date('h:i A', strtotime($row['end']));
        
        $slots[] = [
            'id' => $row['id'],
            'start' => $startTime,
            'end' => $endTime
        ];
    }
    
    echo json_encode($slots);
} else {
    // If no date specified, return all available dates
    $query = "SELECT DISTINCT available_date 
              FROM doctor_availability 
              WHERE doctor_id = $doctorId 
              AND available_date >= CURDATE()
              ORDER BY available_date";
    
    $result = $conn->query($query);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
        exit();
    }
    
    $dates = [];
    while ($row = $result->fetch_assoc()) {
        $dates[] = $row['available_date'];
    }
    
    echo json_encode($dates);
}
?>