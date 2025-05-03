<?php
require_once '../includes/auth.php';
header('Content-Type: text/html');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctorId = (int)$_POST['doctor_id'];
    $date = $_POST['date'];
    
    // Get doctor's regular availability
    $stmt = $conn->prepare("
        SELECT DAYOFWEEK(?) AS day, start_time, end_time 
        FROM availability 
        WHERE doctor_id = ?
    ");
    $stmt->bind_param("si", $date, $doctorId);
    $stmt->execute();
    $availability = $stmt->get_result()->fetch_assoc();
    
    // Get booked slots
    $stmt = $conn->prepare("
        SELECT appointment_time 
        FROM appointments 
        WHERE doctor_id = ? 
        AND appointment_date = ? 
        AND status = 'scheduled'
    ");
    $stmt->bind_param("is", $doctorId, $date);
    $stmt->execute();
    $booked = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Generate time slots
    if ($availability) {
        $start = new DateTime($availability['start_time']);
        $end = new DateTime($availability['end_time']);
        $interval = new DateInterval('PT30M');
        
        echo '<div class="time-slots">';
        while ($start <= $end) {
            $time = $start->format('H:i');
            $bookedClass = '';
            
            foreach ($booked as $slot) {
                if ($time === date('H:i', strtotime($slot['appointment_time']))) {
                    $bookedClass = 'disabled';
                    break;
                }
            }
            
            echo "<label class='btn btn-outline-primary time-slot $bookedClass'>
                    <input type='radio' name='appointment_time' value='$time' required>
                    $time
                  </label>";
            $start->add($interval);
        }
        echo '</div>';
    } else {
        echo '<div class="alert alert-warning">No availability for selected date</div>';
    }
}
?>