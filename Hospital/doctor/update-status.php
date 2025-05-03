<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!checkRole('doctor')) {
    header("Location: ../index.php");
    exit();
}

// Validate inputs
$appointmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

if ($appointmentId <= 0 || !in_array($status, ['completed', 'cancelled'])) {
    $_SESSION['error_message'] = "Invalid request parameters";
    header("Location: dashboard.php");
    exit();
}

// Verify the appointment belongs to this doctor
$doctorId = $_SESSION['doctor_id'];
$verifyQuery = $conn->prepare("SELECT appointment_id FROM appointments WHERE appointment_id = ? AND doctor_id = ?");
$verifyQuery->bind_param("ii", $appointmentId, $doctorId);
$verifyQuery->execute();
$verifyQuery->store_result();

if ($verifyQuery->num_rows === 0) {
    $_SESSION['error_message'] = "Appointment not found or not authorized";
    header("Location: dashboard.php");
    exit();
}

// Update the appointment status
$updateQuery = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
$updateQuery->bind_param("si", $status, $appointmentId);

if ($updateQuery->execute()) {
    $_SESSION['success_message'] = "Appointment marked as " . ucfirst($status) . " successfully!";
} else {
    $_SESSION['error_message'] = "Error updating appointment status: " . $conn->error;
}

header("Location: dashboard.php");
exit();
?>