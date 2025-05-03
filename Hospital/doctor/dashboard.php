<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';

if (!checkRole('doctor')) {
    header("Location: ../index.php");
    exit();
}

// Get doctor ID from session
$userId = $_SESSION['user_id'];
$doctorQuery = $conn->query("SELECT doctor_id FROM doctors WHERE user_id = $userId");
$doctorData = $doctorQuery->fetch_assoc();
$doctorId = $doctorData['doctor_id'];

// Store doctor_id in session for later use
$_SESSION['doctor_id'] = $doctorId;

// Get upcoming appointments
$appointments = $conn->query("
    SELECT a.*, u.full_name as patient_name 
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN users u ON p.user_id = u.user_id
    WHERE a.doctor_id = $doctorId
    AND a.status = 'scheduled'
    ORDER BY a.appointment_date, a.appointment_time
    LIMIT 10
");

// Get appointment statistics
$totalAppointments = $conn->query("SELECT COUNT(*) FROM appointments WHERE doctor_id = $doctorId")->fetch_row()[0];
$pendingAppointments = $conn->query("SELECT COUNT(*) FROM appointments WHERE doctor_id = $doctorId AND status = 'scheduled'")->fetch_row()[0];
$completedAppointments = $conn->query("SELECT COUNT(*) FROM appointments WHERE doctor_id = $doctorId AND status = 'completed'")->fetch_row()[0];
?>

<div class="row">
    <!-- Doctor Stats -->
    <div class="col-md-4 mb-4">
        <div class="card" style="background-color: rgba(255, 255, 255, 0.8);">
            <div class="card-header" style="background-color: rgba(13, 110, 253, 0.0);">
                <h5 class="mb-0 text-primary">My Statistics</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total Appointments
                        <span class="badge bg-primary"><?= $totalAppointments ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Completed Appointments
                        <span class="badge bg-success"><?= $completedAppointments ?></span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card mt-4" style="background-color: rgba(255, 255, 255, 0.8);">
            <div class="card-header" style="background-color: rgba(13, 110, 253, 0.0);">
                <h5 class="mb-0 text-primary">Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="manage-availability.php" class="btn btn-outline-primary mb-2 w-100">Manage Availability</a>
                <a href="appointment-history.php" class="btn btn-outline-secondary mb-2 w-100">View All Appointments</a>
                <a href="profile.php" class="btn btn-outline-info w-100">Update Profile</a>
            </div>
        </div>
    </div>

    <!-- Upcoming Appointments -->
    <div class="col-md-8">
        <div class="card" style="background-color: rgba(255, 255, 255, 0.8); height: 500px; display: flex; flex-direction: column;">
            <div class="card-header" style="background-color: rgba(13, 110, 253, 0.0);">
                <h5 class="mb-0 text-primary">Upcoming Appointments</h5>
            </div>
            <div class="card-body" style="flex: 1; overflow: hidden; display: flex; flex-direction: column;">
                <div style="max-height: 100%; overflow-y: auto; flex: 1;">
                    <?php if ($appointments->num_rows > 0): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $appointments->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['patient_name']) ?></td>
                                <td><?= date('M j, Y', strtotime($row['appointment_date'])) ?></td>
                                <td><?= date('h:i A', strtotime($row['appointment_time'])) ?></td>
                                <td><?= htmlspecialchars($row['reason']) ?></td>
                                <td>
                                    <div class="d-flex gap-2" role="group">
                                        <a href="update-status.php?id=<?= $row['appointment_id'] ?>&status=completed" class="btn btn-sm btn-success">Complete</a>
                                        <a href="update-status.php?id=<?= $row['appointment_id'] ?>&status=cancelled" class="btn btn-sm btn-danger">Cancel</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info">
                        No upcoming appointments scheduled.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>