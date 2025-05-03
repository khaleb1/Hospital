<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';

if (!checkRole('doctor')) {
    header("Location: ../index.php");
    exit();
}

// Get current availability
$doctorId = $_SESSION['doctor_id'];
$availability = $conn->query("
    SELECT * FROM doctor_availability 
    WHERE doctor_id = $doctorId AND available_date >= CURDATE()
    ORDER BY available_date, start_time
");
?>

<div class="card" style="background-color: rgba(255, 255, 255, 0.8);">
    <div class="card-header" style="background-color: rgba(13, 110, 253, 0.0);">
        <h5 class="mb-0 text-primary">Manage Date-Specific Availability</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="save-availability.php">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Date</label>
                    <input type="date" name="available_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Time</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Time</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Add Slot</button>
                </div>
            </div>
        </form>

        <h6 class="mt-4">Upcoming Available Slots</h6>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($slot = $availability->fetch_assoc()): ?>
                <tr>
                    <td><?= date('M j, Y', strtotime($slot['available_date'])) ?></td>
                    <td><?= date('h:i A', strtotime($slot['start_time'])) ?></td>
                    <td><?= date('h:i A', strtotime($slot['end_time'])) ?></td>
                    <td>
                        <a href="delete-availability.php?id=<?= $slot['availability_id'] ?>" class="btn btn-sm btn-danger">Remove</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>