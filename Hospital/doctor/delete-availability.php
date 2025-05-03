<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!checkRole('doctor')) {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $slotId = (int)$_GET['id'];
    $doctorId = $_SESSION['doctor_id'];
    
    // Verify the slot belongs to this doctor before deleting
    $conn->query("DELETE FROM doctor_availability 
                 WHERE availability_id = $slotId AND doctor_id = $doctorId");
    
    $_SESSION['success'] = "Availability slot removed successfully";
}

header("Location: manage-availability.php");
exit();
?>