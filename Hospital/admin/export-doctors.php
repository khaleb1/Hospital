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
header('Content-Disposition: attachment; filename="doctors_export_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
fputcsv($output, ['Doctor ID', 'Full Name', 'Email', 'Phone', 'Specialization', 'Qualifications', 'Status']);

// Get all doctors with user information
$query = "SELECT d.doctor_id, u.full_name, u.email, u.phone, d.specialization, d.qualifications, u.status 
          FROM doctors d
          JOIN users u ON d.user_id = u.user_id
          ORDER BY d.doctor_id";
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