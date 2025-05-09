<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle patient actions (activate, deactivate, delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $patientId = $_GET['id'];
    
    switch ($action) {
        case 'activate':
            $stmt = $conn->prepare("UPDATE users u JOIN patients p ON u.user_id = p.user_id SET u.status = 'active' WHERE p.patient_id = ?");
            $stmt->bind_param("i", $patientId);
            $stmt->execute();
            $_SESSION['success_msg'] = "Patient activated successfully.";
            break;
            
        case 'deactivate':
            $stmt = $conn->prepare("UPDATE users u JOIN patients p ON u.user_id = p.user_id SET u.status = 'inactive' WHERE p.patient_id = ?");
            $stmt->bind_param("i", $patientId);
            $stmt->execute();
            $_SESSION['success_msg'] = "Patient deactivated successfully.";
            break;
            
        case 'delete':
            // First get the user_id associated with this patient
            $stmt = $conn->prepare("SELECT user_id FROM patients WHERE patient_id = ?");
            $stmt->bind_param("i", $patientId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $userId = $row['user_id'];
                
                // Begin transaction
                $conn->begin_transaction();
                
                try {
                    // Check if patient has appointments
                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ?");
                    $stmt->bind_param("i", $patientId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    
                    if ($row['count'] > 0) {
                        throw new Exception("Cannot delete patient with existing appointments. Please delete the appointments first.");
                    }
                    
                    // Delete from patients table first
                    $stmt = $conn->prepare("DELETE FROM patients WHERE patient_id = ?");
                    $stmt->bind_param("i", $patientId);
                    $stmt->execute();
                    
                    // Then delete from users table
                    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    
                    // Commit transaction
                    $conn->commit();
                    $_SESSION['success_msg'] = "Patient deleted successfully.";
                } catch (Exception $e) {
                    // Rollback in case of error
                    $conn->rollback();
                    $_SESSION['error_msg'] = "Error deleting patient: " . $e->getMessage();
                }
            }
            break;
    }
    
    header("Location: manage-patients.php");
    exit();
}

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Prepare base query
$query = "SELECT p.patient_id, u.full_name, u.email, u.phone, p.date_of_birth, p.gender, p.blood_group, u.status, u.created_at 
          FROM patients p
          JOIN users u ON p.user_id = u.user_id
          WHERE 1=1";

// Add search conditions
if (!empty($search)) {
    $search = "%$search%";
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
}

if (!empty($gender)) {
    $query .= " AND p.gender = ?";
}

if (!empty($status)) {
    $query .= " AND u.status = ?";
}

// Add ordering
$query .= " ORDER BY p.patient_id DESC";

// Prepare and execute the statement
$stmt = $conn->prepare($query);

// Bind parameters based on search conditions
if (!empty($search) && !empty($gender) && !empty($status)) {
    $stmt->bind_param("ssss", $search, $search, $gender, $status);
} elseif (!empty($search) && !empty($gender)) {
    $stmt->bind_param("sss", $search, $search, $gender);
} elseif (!empty($search) && !empty($status)) {
    $stmt->bind_param("sss", $search, $search, $status);
} elseif (!empty($gender) && !empty($status)) {
    $stmt->bind_param("ss", $gender, $status);
} elseif (!empty($search)) {
    $stmt->bind_param("ss", $search, $search);
} elseif (!empty($gender)) {
    $stmt->bind_param("s", $gender);
} elseif (!empty($status)) {
    $stmt->bind_param("s", $status);
}

$stmt->execute();
$result = $stmt->get_result();

require_once '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="card shadow-sm" style="background-color: rgba(255, 255, 255, 0.8);">
        <div class="card-header" style="background-color: rgba(13, 110, 253, 0.0);">
            <h5 class="mb-0 text-primary">Manage Patients</h5>
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
            <form method="GET" action="manage-patients.php" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="gender">
                            <option value="">All Genders</option>
                            <option value="Male" <?= ($gender === 'Male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($gender === 'Female') ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= ($gender === 'Other') ? 'selected' : '' ?>>Other</option>
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
            
            <!-- Add New Patient Button -->
            <div class="mb-3">
                <a href="add-patient.php" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Add New Patient
                </a>
            </div>
            
            <!-- Patients Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Date of Birth</th>
                            <th>Gender</th>
                            <th>Blood Group</th>
                            <th>Status</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($patient = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $patient['patient_id'] ?></td>
                                    <td><?= htmlspecialchars($patient['full_name']) ?></td>
                                    <td><?= htmlspecialchars($patient['email']) ?></td>
                                    <td><?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></td>
                                    <td><?= $patient['date_of_birth'] ? date('M d, Y', strtotime($patient['date_of_birth'])) : 'N/A' ?></td>
                                    <td><?= htmlspecialchars($patient['gender'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($patient['blood_group'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge <?= ($patient['status'] === 'active') ? 'bg-success' : 'bg-warning' ?>">
                                            <?= ucfirst($patient['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($patient['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit-patient.php?id=<?= $patient['patient_id'] ?>" class="btn btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($patient['status'] === 'active'): ?>
                                                <a href="manage-patients.php?action=deactivate&id=<?= $patient['patient_id'] ?>" 
                                                   class="btn btn-warning" 
                                                   onclick="return confirm('Are you sure you want to deactivate this patient?')">
                                                    <i class="fas fa-user-slash"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="manage-patients.php?action=activate&id=<?= $patient['patient_id'] ?>" 
                                                   class="btn btn-success" 
                                                   onclick="return confirm('Are you sure you want to activate this patient?')">
                                                    <i class="fas fa-user-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="manage-patients.php?action=delete&id=<?= $patient['patient_id'] ?>" 
                                               class="btn btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this patient? This action cannot be undone.')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">No patients found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>