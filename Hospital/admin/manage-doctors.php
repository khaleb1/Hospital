<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle doctor actions (activate, deactivate, delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $doctorId = $_GET['id'];
    
    switch ($action) {
        case 'activate':
            $stmt = $conn->prepare("UPDATE users u JOIN doctors d ON u.user_id = d.user_id SET u.status = 'active' WHERE d.doctor_id = ?");
            $stmt->bind_param("i", $doctorId);
            $stmt->execute();
            $_SESSION['success_msg'] = "Doctor activated successfully.";
            break;
            
        case 'deactivate':
            $stmt = $conn->prepare("UPDATE users u JOIN doctors d ON u.user_id = d.user_id SET u.status = 'inactive' WHERE d.doctor_id = ?");
            $stmt->bind_param("i", $doctorId);
            $stmt->execute();
            $_SESSION['success_msg'] = "Doctor deactivated successfully.";
            break;
            
        case 'delete':
            // First get the user_id associated with this doctor
            $stmt = $conn->prepare("SELECT user_id FROM doctors WHERE doctor_id = ?");
            $stmt->bind_param("i", $doctorId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $userId = $row['user_id'];
                
                // Begin transaction
                $conn->begin_transaction();
                
                try {
                    // Delete from doctors table first
                    $stmt = $conn->prepare("DELETE FROM doctors WHERE doctor_id = ?");
                    $stmt->bind_param("i", $doctorId);
                    $stmt->execute();
                    
                    // Then delete from users table
                    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    
                    // Commit transaction
                    $conn->commit();
                    $_SESSION['success_msg'] = "Doctor deleted successfully.";
                } catch (Exception $e) {
                    // Rollback in case of error
                    $conn->rollback();
                    $_SESSION['error_msg'] = "Error deleting doctor: " . $e->getMessage();
                }
            }
            break;
    }
    
    header("Location: manage-doctors.php");
    exit();
}

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$specialization = isset($_GET['specialization']) ? $_GET['specialization'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Prepare base query
$query = "SELECT d.doctor_id, u.full_name, u.email, u.phone, d.specialization, u.status, u.created_at 
          FROM doctors d
          JOIN users u ON d.user_id = u.user_id
          WHERE 1=1";

// Add search conditions
if (!empty($search)) {
    $search = "%$search%";
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
}

if (!empty($specialization)) {
    $query .= " AND d.specialization = ?";
}

if (!empty($status)) {
    $query .= " AND u.status = ?";
}

// Add ordering
$query .= " ORDER BY d.doctor_id DESC";

// Prepare and execute the statement
$stmt = $conn->prepare($query);

// Bind parameters based on search conditions
if (!empty($search) && !empty($specialization) && !empty($status)) {
    $stmt->bind_param("ssss", $search, $search, $specialization, $status);
} elseif (!empty($search) && !empty($specialization)) {
    $stmt->bind_param("sss", $search, $search, $specialization);
} elseif (!empty($search) && !empty($status)) {
    $stmt->bind_param("sss", $search, $search, $status);
} elseif (!empty($specialization) && !empty($status)) {
    $stmt->bind_param("ss", $specialization, $status);
} elseif (!empty($search)) {
    $stmt->bind_param("ss", $search, $search);
} elseif (!empty($specialization)) {
    $stmt->bind_param("s", $specialization);
} elseif (!empty($status)) {
    $stmt->bind_param("s", $status);
}

$stmt->execute();
$result = $stmt->get_result();

// Get all specializations for the filter dropdown
$specializationsQuery = "SELECT DISTINCT specialization FROM doctors ORDER BY specialization";
$specializationsResult = $conn->query($specializationsQuery);
$specializations = [];
while ($row = $specializationsResult->fetch_assoc()) {
    $specializations[] = $row['specialization'];
}

require_once '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="card shadow-sm" style="background-color: rgba(255, 255, 255, 0.8);">
        <div class="card-header" style="background-color: rgba(13, 110, 253, 0.0);">
            <h5 class="mb-0 text-primary">Manage Doctors</h5>
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
            <form method="GET" action="manage-doctors.php" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="specialization">
                            <option value="">All Specializations</option>
                            <?php foreach ($specializations as $spec): ?>
                                <option value="<?= htmlspecialchars($spec) ?>" <?= ($specialization === $spec) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($spec) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
            
            <!-- Add New Doctor Button -->
            <div class="mb-3">
                <a href="add-doctor.php" class="btn btn-success">
                    <i class="fas fa-user-md"></i> Add New Doctor
                </a>
            </div>
            
            <!-- Doctors Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Specialization</th>
                            <th>Status</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($doctor = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $doctor['doctor_id'] ?></td>
                                    <td><?= htmlspecialchars($doctor['full_name']) ?></td>
                                    <td><?= htmlspecialchars($doctor['email']) ?></td>
                                    <td><?= htmlspecialchars($doctor['phone'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($doctor['specialization']) ?></td>
                                    <td>
                                        <span class="badge <?= ($doctor['status'] === 'active') ? 'bg-success' : 'bg-warning' ?>">
                                            <?= ucfirst($doctor['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($doctor['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Actions buttons -->
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No doctors found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>