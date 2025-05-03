<?php
require_once '../includes/auth.php';
require_once '../includes/email_template.php';

if (!checkRole('patient')) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_SESSION['user_id'];
    $doctor_id = (int)$_POST['doctor_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $reason = htmlspecialchars($_POST['reason']);

    // Check availability
    $stmt = $conn->prepare("SELECT * FROM availability 
                          WHERE doctor_id = ? 
                          AND DAYOFWEEK(?) = day_of_week
                          AND ? BETWEEN start_time AND end_time");
    $stmt->bind_param("iss", $doctor_id, $date, $time);
    $stmt->execute();
    $available = $stmt->get_result()->num_rows > 0;

    if (!$available) {
        header("Location: book-appointment.php?error=slot_taken");
        exit();
    }

    // Insert appointment
    $stmt = $conn->prepare("INSERT INTO appointments 
                          (patient_id, doctor_id, appointment_date, appointment_time, reason) 
                          VALUES ((SELECT patient_id FROM patients WHERE user_id = ?), ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $patient_id, $doctor_id, $date, $time, $reason);

    if ($stmt->execute()) {
        // Get patient and doctor details
        $result = $conn->query("
            SELECT 
                p.user_id as patient_user_id,
                u.full_name as patient_name,
                u.email as patient_email,
                d.full_name as doctor_name,
                d.email as doctor_email
            FROM patients p
            JOIN users u ON p.user_id = u.user_id
            JOIN doctors doc ON doc.doctor_id = $doctor_id
            JOIN users d ON doc.user_id = d.user_id
            WHERE p.user_id = $patient_id
        ")->fetch_assoc();

        // Send confirmation to patient
        $patientDetails = [
            'patient_name' => $result['patient_name'],
            'doctor' => $result['doctor_name'],
            'date' => date('F j, Y', strtotime($date)),
            'time' => date('g:i a', strtotime($time)),
            'reason' => $reason
        ];
        sendAppointmentNotification($result['patient_email'], 'confirmation', $patientDetails);

        // Send notification to doctor
        $doctorDetails = [
            'patient_name' => $result['patient_name'],
            'doctor' => $result['doctor_name'],
            'date' => date('F j, Y', strtotime($date)),
            'time' => date('g:i a', strtotime($time)),
            'reason' => $reason
        ];
        sendAppointmentNotification($result['doctor_email'], 'confirmation', $doctorDetails);

        header("Location: appointments.php?success=booked");
        exit();
    } else {
        header("Location: book-appointment.php?error=booking_failed");
        exit();
    }
}