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
$tableCheck = $conn->query("SHOW TABLES LIKE 'availability'");
if ($tableCheck->num_rows === 0) {
    http_response_code(500);
    echo json_encode(['error' => 'Availability table does not exist']);
    exit();
}

// Let's check if there's any data for this doctor
$dataCheck = $conn->query("SELECT COUNT(*) as count FROM availability WHERE doctor_id = $doctorId");
$row = $dataCheck->fetch_assoc();
if ($row['count'] == 0) {
    http_response_code(200);
    echo json_encode(['error' => 'No availability data found for this doctor', 'doctor_id' => $doctorId]);
    exit();
}

// --- Check if a specific date is provided to fetch times ---
if (isset($_GET['available_date'])) {
    // --- Scenario 2: Fetch Times for a Specific Date ---
    $selectedDate = $_GET['available_date'];

    // Basic validation for the date format (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid date format. Please use YYYY-MM-DD.']);
        exit();
    }

    try {
        // Query for available time slots on the selected date
        $query = "SELECT availability_id, start_time, end_time 
                  FROM availability 
                  WHERE doctor_id = ? 
                    AND available_date = ?
                    AND status = 'available'
                  ORDER BY start_time ASC";
                  
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Time query preparation failed: ' . $conn->error]);
            exit();
        }
        
        $stmt->bind_param("is", $doctorId, $selectedDate);
        
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Time query execution failed: ' . $stmt->error]);
            exit();
        }
        
        $result = $stmt->get_result();
        
        $slots = [];
        while ($row = $result->fetch_assoc()) {
            $slots[] = [
                'id' => $row['availability_id'],
                'start' => date("h:i A", strtotime($row['start_time'])),
                'end' => date("h:i A", strtotime($row['end_time']))
            ];
        }
        
        $stmt->close();
        $conn->close(); 

        echo json_encode($slots);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'An unexpected error occurred while fetching times: ' . $e->getMessage()]);
    }

} else {
    // --- Scenario 1: Fetch Available Dates (Existing Logic) ---
    try {
        // Query for distinct available dates
        $query = "SELECT DISTINCT available_date 
                  FROM availability 
                  WHERE doctor_id = ? 
                    AND available_date >= CURDATE()
                    AND status = 'available'
                  ORDER BY available_date ASC";
                  
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Date query preparation failed: ' . $conn->error]);
            exit();
        }
        
        $stmt->bind_param("i", $doctorId);
        
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Date query execution failed: ' . $stmt->error]);
            exit();
        }
        
        $result = $stmt->get_result();
        
        $dates = [];
        while ($row = $result->fetch_assoc()) {
            $dates[] = $row['available_date']; 
        }
        
        $stmt->close();
        $conn->close(); 

        echo json_encode($dates);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'An unexpected error occurred while fetching dates: ' . $e->getMessage()]);
    }
}
?>