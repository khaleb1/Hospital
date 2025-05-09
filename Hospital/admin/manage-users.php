<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle user actions (activate, deactivate, delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $userId = $_GET['id'];
    
    switch ($action) {
        case 'activate':
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $_SESSION['success_msg'] = "User activated successfully.";
            break;
            
        case 'deactivate':
            $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $_SESSION['success_msg'] = "User deactivated successfully.";
            break;
            
        case 'delete':
            // Check if user can be safely deleted
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $_SESSION['success_msg'] = "User deleted successfully.";
            break;
    }
    
    header("Location: manage-users.php");
    exit();
}

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Prepare base query for counting total records
$count_query = "SELECT COUNT(*) as total FROM users WHERE 1=1";

// Add search conditions to count query
if (!empty($search)) {
    $search_pattern = "%$search%";
    $count_query .= " AND (full_name LIKE ? OR email LIKE ?)";
}

if (!empty($role)) {
    $count_query .= " AND role = ?";
}

if (!empty($status)) {
    $count_query .= " AND status = ?";
}

// Prepare and execute the count statement
$count_stmt = $conn->prepare($count_query);

// Bind parameters for count query
if (!empty($search) && !empty($role) && !empty($status)) {
    $count_stmt->bind_param("sss", $search_pattern, $search_pattern, $role, $status);
} elseif (!empty($search) && !empty($role)) {
    $count_stmt->bind_param("sss", $search_pattern, $search_pattern, $role);
} elseif (!empty($search) && !empty($status)) {
    $count_stmt->bind_param("sss", $search_pattern, $search_pattern, $status);
} elseif (!empty($role) && !empty($status)) {
    $count_stmt->bind_param("ss", $role, $status);
} elseif (!empty($search)) {
    $count_stmt->bind_param("ss", $search_pattern, $search_pattern);
} elseif (!empty($role)) {
    $count_stmt->bind_param("s", $role);
} elseif (!empty($status)) {
    $count_stmt->bind_param("s", $status);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Prepare base query for fetching records with pagination
$query = "SELECT user_id, full_name, email, phone, role, status, created_at FROM users WHERE 1=1";

// Add search conditions
if (!empty($search)) {
    $search = "%$search%";
    $query .= " AND (full_name LIKE ? OR email LIKE ?)";
}

if (!empty($role)) {
    $query .= " AND role = ?";
}

if (!empty($status)) {
    $query .= " AND status = ?";
}

// Add ordering and pagination
$query .= " ORDER BY user_id DESC LIMIT ? OFFSET ?";

// Prepare and execute the statement
$stmt = $conn->prepare($query);

// Bind parameters based on search conditions
if (!empty($search) && !empty($role) && !empty($status)) {
    $stmt->bind_param("sssii", $search, $search, $role, $status, $records_per_page, $offset);
} elseif (!empty($search) && !empty($role)) {
    $stmt->bind_param("sssii", $search, $search, $role, $records_per_page, $offset);
} elseif (!empty($search) && !empty($status)) {
    $stmt->bind_param("sssii", $search, $search, $status, $records_per_page, $offset);
} elseif (!empty($role) && !empty($status)) {
    $stmt->bind_param("ssii", $role, $status, $records_per_page, $offset);
} elseif (!empty($search)) {
    $stmt->bind_param("ssii", $search, $search, $records_per_page, $offset);
} elseif (!empty($role)) {
    $stmt->bind_param("sii", $role, $records_per_page, $offset);
} elseif (!empty($status)) {
    $stmt->bind_param("sii", $status, $records_per_page, $offset);
} else {
    $stmt->bind_param("ii", $records_per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

require_once '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="card shadow-sm" style="background-color: rgba(255, 255, 255, 0.8);">
        <div class="card-header" style="background-color: rgba(13, 110, 253, 0.0);">
            <h5 class="mb-0 text-primary">Manage Users</h5>
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
            
            <!-- Search and Filter Form -->
            <form method="GET" action="manage-users.php" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="role">
                            <option value="">All Roles</option>
                            <option value="admin" <?= ($role === 'admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="doctor" <?= ($role === 'doctor') ? 'selected' : '' ?>>Doctor</option>
                            <option value="patient" <?= ($role === 'patient') ? 'selected' : '' ?>>Patient</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            <option value="pending" <?= ($status === 'pending') ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
            
            <!-- Add New User Button -->
            <div class="mb-3">
                <a href="add-user.php" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Add New User
                </a>
            </div>
            
            <!-- Users Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($user = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $user['user_id'] ?></td>
                                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            echo match($user['role']) {
                                                'admin' => 'bg-danger',
                                                'doctor' => 'bg-success',
                                                'patient' => 'bg-info',
                                                default => 'bg-secondary'
                                            };
                                        ?>">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php 
                                            echo match($user['status']) {
                                                'active' => 'bg-success',
                                                'inactive' => 'bg-warning',
                                                'pending' => 'bg-secondary',
                                                default => 'bg-secondary'
                                            };
                                        ?>">
                                            <?= ucfirst($user['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit-user.php?id=<?= $user['user_id'] ?>" class="btn btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['status'] === 'active'): ?>
                                                <a href="manage-users.php?action=deactivate&id=<?= $user['user_id'] ?>" 
                                                   class="btn btn-warning" 
                                                   onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                    <i class="fas fa-user-slash"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="manage-users.php?action=activate&id=<?= $user['user_id'] ?>" 
                                                   class="btn btn-success" 
                                                   onclick="return confirm('Are you sure you want to activate this user?')">
                                                    <i class="fas fa-user-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="manage-users.php?action=delete&id=<?= $user['user_id'] ?>" 
                                               class="btn btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1<?= !empty($search) ? '&search='.urlencode($search) : '' ?><?= !empty($role) ? '&role='.urlencode($role) : '' ?><?= !empty($status) ? '&status='.urlencode($status) : '' ?>" aria-label="First">
                                <span aria-hidden="true">&laquo;&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page-1 ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?><?= !empty($role) ? '&role='.urlencode($role) : '' ?><?= !empty($status) ? '&status='.urlencode($status) : '' ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    // Calculate range of page numbers to display
                    $range = 2; // Display 2 pages before and after current page
                    $start_page = max(1, $page - $range);
                    $end_page = min($total_pages, $page + $range);
                    
                    // Always show first page
                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=1' . 
                            (!empty($search) ? '&search='.urlencode($search) : '') . 
                            (!empty($role) ? '&role='.urlencode($role) : '') . 
                            (!empty($status) ? '&status='.urlencode($status) : '') . 
                            '">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                        }
                    }
                    
                    // Display page numbers
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">
                                <a class="page-link" href="?page=' . $i . 
                                (!empty($search) ? '&search='.urlencode($search) : '') . 
                                (!empty($role) ? '&role='.urlencode($role) : '') . 
                                (!empty($status) ? '&status='.urlencode($status) : '') . 
                                '">' . $i . '</a>
                              </li>';
                    }
                    
                    // Always show last page
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . 
                            (!empty($search) ? '&search='.urlencode($search) : '') . 
                            (!empty($role) ? '&role='.urlencode($role) : '') . 
                            (!empty($status) ? '&status='.urlencode($status) : '') . 
                            '">' . $total_pages . '</a></li>';
                    }
                    ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page+1 ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?><?= !empty($role) ? '&role='.urlencode($role) : '' ?><?= !empty($status) ? '&status='.urlencode($status) : '' ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $total_pages ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?><?= !empty($role) ? '&role='.urlencode($role) : '' ?><?= !empty($status) ? '&status='.urlencode($status) : '' ?>" aria-label="Last">
                                <span aria-hidden="true">&raquo;&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="text-center text-muted">
                Showing <?= min(($page - 1) * $records_per_page + 1, $total_records) ?> to <?= min($page * $records_per_page, $total_records) ?> of <?= $total_records ?> users
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>