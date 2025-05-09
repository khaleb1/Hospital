<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle appointment actions (complete, cancel, delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $appointmentId = $_GET['id'];
    
    switch ($action) {
        case 'complete':
            $stmt = $conn->prepare("UPDATE appointments SET status = 'completed' WHERE appointment_id = ?");
            $stmt->bind_param("i", $appointmentId);
            $stmt->execute();
            $_SESSION['success_msg'] = "Appointment marked as completed.";
            break;
            
        case 'cancel':
            $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ?");
            $stmt->bind_param("i", $appointmentId);
            $stmt->execute();
            $_SESSION['success_msg'] = "Appointment cancelled successfully.";
            break;
            
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
            $stmt->bind_param("i", $appointmentId);
            $stmt->execute();
            $_SESSION['success_msg'] = "Appointment deleted successfully.";
            break;
    }
    
    header("Location: manage-appointments.php");
    exit();
}

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$doctor = isset($_GET['doctor']) ? $_GET['doctor'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Prepare base query
$query = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status, a.reason, a.notes,
                 p.patient_id, d.doctor_id, 
                 p_user.full_name AS patient_name, d_user.full_name AS doctor_name,
                 d.specialization
          FROM appointments a
          JOIN patients p ON a.patient_id = p.patient_id
          JOIN users p_user ON p.user_id = p_user.user_id
          JOIN doctors d ON a.doctor_id = d.doctor_id
          JOIN users d_user ON d.user_id = d_user.user_id
          WHERE 1=1";

// Add search conditions
if (!empty($search)) {
    $search = "%$search%";
    $query .= " AND (p_user.full_name LIKE ? OR d_user.full_name LIKE ? OR a.reason LIKE ?)";
}

if (!empty($status)) {
    $query .= " AND a.status = ?";
}

if (!empty($doctor)) {
    $query .= " AND d.doctor_id = ?";
}

if (!empty($date_from)) {
    $query .= " AND a.appointment_date >= ?";
}

if (!empty($date_to)) {
    $query .= " AND a.appointment_date <= ?";
}

// Add ordering
$query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

// Prepare and execute the statement
$stmt = $conn->prepare($query);

// Create an array to hold parameters
$params = [];
$types = "";

// Add parameters based on search conditions
if (!empty($search)) {
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= "sss";
}

if (!empty($status)) {
    $params[] = $status;
    $types .= "s";
}

if (!empty($doctor)) {
    $params[] = $doctor;
    $types .= "i";
}

if (!empty($date_from)) {
    $params[] = $date_from;
    $types .= "s";
}

if (!empty($date_to)) {
    $params[] = $date_to;
    $types .= "s";
}

// Bind parameters if there are any
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Get all doctors for filter dropdown
$doctors = $conn->query("SELECT d.doctor_id, u.full_name 
                         FROM doctors d 
                         JOIN users u ON d.user_id = u.user_id 
                         ORDER BY u.full_name");

require_once '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="card shadow-sm" style="background-color: rgba(255, 255, 255, 0.8);">
        <div class="card-header" style="background-color: rgba(13, 110, 253, 0.0);">
            <h5 class="mb-0 text-primary">Manage Appointments</h5>
        </div>
        <div class="card-body">
            <!-- Success Message -->
            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_msg'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_msg']); ?>
            <?php endif; ?>
            
            <!-- Error Message -->
            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_msg'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_msg']); ?>
            <?php endif; ?>
            
            <!-- Search and Filter Form -->
            <form method="GET" action="manage-appointments.php" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Search by name or reason" value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="scheduled" <?= ($status === 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
                            <option value="completed" <?= ($status === 'completed') ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= ($status === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="doctor">
                            <option value="">All Doctors</option>
                            <?php while ($doc = $doctors->fetch_assoc()): ?>
                                <option value="<?= $doc['doctor_id'] ?>" <?= ($doctor == $doc['doctor_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($doc['full_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from" placeholder="From Date" value="<?= htmlspecialchars($date_from ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_to" placeholder="To Date" value="<?= htmlspecialchars($date_to ?? '') ?>">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
            
            <!-- Export Button -->
            <div class="mb-3">
                <a href="export-appointments.php" class="btn btn-success">
                    <i class="fas fa-file-export"></i> Export to CSV
                </a>
            </div>
            
            <!-- Appointments Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Specialization</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($appointment = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $appointment['appointment_id'] ?></td>
                                    <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                                    <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                                    <td><?= htmlspecialchars($appointment['specialization']) ?></td>
                                    <td><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></td>
                                    <td><?= date('h:i A', strtotime($appointment['appointment_time'])) ?></td>
                                    <td>
                                        <span class="badge <?= 
                                            ($appointment['status'] === 'scheduled') ? 'bg-primary' : 
                                            (($appointment['status'] === 'completed') ? 'bg-success' : 'bg-danger') 
                                        ?>">
                                            <?= ucfirst($appointment['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($appointment['reason']) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($appointment['status'] === 'scheduled'): ?>
                                                <a href="manage-appointments.php?action=complete&id=<?= $appointment['appointment_id'] ?>" 
                                                   class="btn btn-success" 
                                                   onclick="return confirm('Mark this appointment as completed?')">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="manage-appointments.php?action=cancel&id=<?= $appointment['appointment_id'] ?>" 
                                                   class="btn btn-warning" 
                                                   onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="manage-appointments.php?action=delete&id=<?= $appointment['appointment_id'] ?>" 
                                               class="btn btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this appointment? This action cannot be undone.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No appointments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>