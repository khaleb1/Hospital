<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="appointments_export_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
fputcsv($output, ['Appointment ID', 'Patient Name', 'Doctor Name', 'Appointment Date', 'Appointment Time', 'Status', 'Reason', 'Notes', 'Created At']);

// Get all appointments with patient and doctor information
$query = "SELECT a.appointment_id, p_user.full_name AS patient_name, d_user.full_name AS doctor_name, 
                 a.appointment_date, a.appointment_time, a.status, a.reason, a.notes, a.created_at
          FROM appointments a
          JOIN patients p ON a.patient_id = p.patient_id
          JOIN users p_user ON p.user_id = p_user.user_id
          JOIN doctors d ON a.doctor_id = d.doctor_id
          JOIN users d_user ON d.user_id = d_user.user_id
          ORDER BY a.appointment_date DESC, a.appointment_time ASC";
$result = $conn->query($query);

// Write data rows
while ($row = $result->fetch_assoc()) {
    // Clean data for CSV
    $row = array_map(function($value) {
        return $value ?? 'N/A'; // Replace null values with N/A
    }, $row);
    
    fputcsv($output, $row);
}

// Close the output stream
fclose($output);
exit();
?>