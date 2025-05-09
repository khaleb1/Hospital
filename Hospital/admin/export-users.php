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
header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
fputcsv($output, ['User ID', 'Full Name', 'Email', 'Phone', 'Role', 'Status', 'Registration Date']);

// Get all users from database
$query = "SELECT user_id, full_name, email, phone, role, status, created_at FROM users ORDER BY user_id";
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