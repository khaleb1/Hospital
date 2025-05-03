<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!checkRole('doctor')) {
    header("Location: ../index.php");
    exit();
}

$doctorId = $_SESSION['doctor_id'];
$date = $_POST['available_date'];
$start = $_POST['start_time'];
$end = $_POST['end_time'];

// Check for conflicts
// Modified conflict check query
$conflict = $conn->query("
    SELECT 1 FROM doctor_availability 
    WHERE doctor_id = $doctorId 
    AND available_date = '$date'
    AND (
        (start_time < '$end' AND end_time > '$start')
    )
")->num_rows;

if ($conflict > 0) {
    $_SESSION['error'] = "This time slot conflicts with existing availability";
    header("Location: manage-availability.php");
    exit();
}

$stmt = $conn->prepare("
    INSERT INTO doctor_availability (doctor_id, available_date, start_time, end_time)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("isss", $doctorId, $date, $start, $end);
$stmt->execute();

$_SESSION['success'] = "Availability slot added successfully";
header("Location: manage-availability.php");