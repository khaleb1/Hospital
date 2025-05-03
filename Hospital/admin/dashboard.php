<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/header.php';

// Get dashboard statistics
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$totalDoctors = $conn->query("SELECT COUNT(*) FROM doctors")->fetch_row()[0];
$totalPatients = $conn->query("SELECT COUNT(*) FROM patients")->fetch_row()[0];
$totalAppointments = $conn->query("SELECT COUNT(*) FROM appointments")->fetch_row()[0];
$pendingAppointments = $conn->query("SELECT COUNT(*) FROM appointments WHERE status = 'scheduled'")->fetch_row()[0];
$completedAppointments = $conn->query("SELECT COUNT(*) FROM appointments WHERE status = 'completed'")->fetch_row()[0];
$cancelledAppointments = $conn->query("SELECT COUNT(*) FROM appointments WHERE status = 'cancelled'")->fetch_row()[0];
?>

<div class="container-fluid mt-4">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</p>
    
    <!-- Admin Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="manage-users.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-users"></i> Manage Users
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="manage-doctors.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-user-md"></i> Manage Doctors
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="manage-patients.php" class="btn btn-outline-info w-100">
                                <i class="fas fa-procedures"></i> Manage Patients
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="manage-appointments.php" class="btn btn-outline-warning w-100">
                                <i class="fas fa-calendar-check"></i> Manage Appointments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalUsers ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Doctors</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalDoctors ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-md fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Patients</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalPatients ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-procedures fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Appointments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalAppointments ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointment Status -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Appointment Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h4 class="text-primary"><?= $pendingAppointments ?></h4>
                            <p>Scheduled</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 class="text-success"><?= $completedAppointments ?></h4>
                            <p>Completed</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 class="text-danger"><?= $cancelledAppointments ?></h4>
                            <p>Cancelled</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Data Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Export Data</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="?export=users&format=csv" class="btn btn-outline-primary w-100">
                                Export Users
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="?export=doctors&format=csv" class="btn btn-outline-success w-100">
                                Export Doctors
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="?export=patients&format=csv" class="btn btn-outline-info w-100">
                                Export Patients
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="?export=appointments&format=csv" class="btn btn-outline-warning w-100">
                                Export Appointments
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>