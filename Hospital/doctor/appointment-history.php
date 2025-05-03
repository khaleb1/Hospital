<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';

if (!checkRole('doctor')) {
    header("Location: ../index.php");
    exit();
}

$doctorId = $_SESSION['doctor_id'];

// Get all appointments (past and future)
$appointments = $conn->query("
    SELECT a.*, u.full_name as patient_name 
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN users u ON p.user_id = u.user_id
    WHERE a.doctor_id = $doctorId
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
?>

<div class="card" style="background-color: rgba(255, 255, 255, 0.8);">
    <div class="card-header" style="background-color: rgba(13, 110, 253, 0.0);">
        <h5 class="mb-0 text-primary">All Appointments</h5>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
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
                    <td>
                        <span class="badge <?= 
                            $row['status'] == 'completed' ? 'bg-success' : 
                            ($row['status'] == 'cancelled' ? 'bg-danger' : 'bg-warning') 
                        ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($row['reason']) ?></td>
                    <td>
                        <a href="view-appointment.php?id=<?= $row['appointment_id'] ?>" class="btn btn-sm btn-info">View</a>
                        <?php if ($row['status'] == 'scheduled'): ?>
                            <a href="update-status.php?id=<?= $row['appointment_id'] ?>&status=completed" class="btn btn-sm btn-success">Complete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>