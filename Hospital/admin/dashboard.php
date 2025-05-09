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

<!-- Custom CSS for this page only -->
<style>
    .dashboard-container {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
    }
    
    .stat-card {
        border-radius: 12px;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .card-indicator {
        height: 8px;
        width: 100%;
    }
    
    .indicator-primary {
        background: linear-gradient(90deg, #4e73df, #6f86e0);
    }
    
    .indicator-success {
        background: linear-gradient(90deg, #1cc88a, #36e2bd);
    }
    
    .indicator-info {
        background: linear-gradient(90deg, #36b9cc, #5dcfdf);
    }
    
    .indicator-warning {
        background: linear-gradient(90deg, #f6c23e, #f8d876);
    }
    
    .action-btn {
        border-radius: 10px;
        padding: 12px;
        transition: all 0.3s ease;
        font-weight: 500;
        border: none;
    }
    
    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .btn-primary-soft {
        background-color: rgba(78, 115, 223, 0.1);
        color: #4e73df;
    }
    
    .btn-success-soft {
        background-color: rgba(28, 200, 138, 0.1);
        color: #1cc88a;
    }
    
    .btn-info-soft {
        background-color: rgba(54, 185, 204, 0.1);
        color: #36b9cc;
    }
    
    .btn-warning-soft {
        background-color: rgba(246, 194, 62, 0.1);
        color: #f6c23e;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.5rem;
    }
    
    .bg-primary-soft {
        background-color: rgba(78, 115, 223, 0.1);
        color: #4e73df;
    }
    
    .bg-success-soft {
        background-color: rgba(28, 200, 138, 0.1);
        color: #1cc88a;
    }
    
    .bg-info-soft {
        background-color: rgba(54, 185, 204, 0.1);
        color: #36b9cc;
    }
    
    .bg-warning-soft {
        background-color: rgba(246, 194, 62, 0.1);
        color: #f6c23e;
    }
    
    .status-pill {
        border-radius: 20px;
        padding: 5px 15px;
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .welcome-card {
        background: linear-gradient(135deg, #4e73df, #224abe);
        color: white;
        border-radius: 12px;
        padding: 25px;
    }
    
    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 20px;
        color: #5a5c69;
    }
    
    .export-card {
        border-radius: 12px;
        background-color: white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
</style>

<div class="container-fluid mt-4">
    <div class="dashboard-container">
        <!-- Welcome Card -->
        <div class="welcome-card mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h3>
                    <p class="mb-0 opacity-75">Here's what's happening with your hospital today.</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="current-date">
                        <?= date('l, F j, Y') ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-indicator indicator-primary"></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="stat-icon bg-primary-soft">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="text-end">
                                <h2 class="mb-0 fw-bold"><?= $totalUsers ?></h2>
                                <p class="text-muted mb-0">Total Users</p>
                            </div>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-indicator indicator-success"></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="stat-icon bg-success-soft">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div class="text-end">
                                <h2 class="mb-0 fw-bold"><?= $totalDoctors ?></h2>
                                <p class="text-muted mb-0">Doctors</p>
                            </div>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 65%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-indicator indicator-info"></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="stat-icon bg-info-soft">
                                <i class="fas fa-procedures"></i>
                            </div>
                            <div class="text-end">
                                <h2 class="mb-0 fw-bold"><?= $totalPatients ?></h2>
                                <p class="text-muted mb-0">Patients</p>
                            </div>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-indicator indicator-warning"></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="stat-icon bg-warning-soft">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="text-end">
                                <h2 class="mb-0 fw-bold"><?= $totalAppointments ?></h2>
                                <p class="text-muted mb-0">Appointments</p>
                            </div>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 55%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Admin Actions & Appointment Status -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h5 class="section-title">Quick Actions</h5>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="manage-users.php" class="btn action-btn btn-primary-soft w-100">
                                    <i class="fas fa-users me-2"></i> Manage Users
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="manage-doctors.php" class="btn action-btn btn-success-soft w-100">
                                    <i class="fas fa-user-md me-2"></i> Manage Doctors
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="manage-patients.php" class="btn action-btn btn-info-soft w-100">
                                    <i class="fas fa-procedures me-2"></i> Manage Patients
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="manage-appointments.php" class="btn action-btn btn-warning-soft w-100">
                                    <i class="fas fa-calendar-check me-2"></i> Manage Appointments
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <h5 class="section-title">Appointment Status</h5>
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="mb-4 text-center">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="status-pill bg-primary-soft">Scheduled</span>
                                <span class="fw-bold"><?= $pendingAppointments ?></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: <?= ($totalAppointments > 0) ? ($pendingAppointments / $totalAppointments * 100) : 0 ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="mb-4 text-center">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="status-pill bg-success-soft">Completed</span>
                                <span class="fw-bold"><?= $completedAppointments ?></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?= ($totalAppointments > 0) ? ($completedAppointments / $totalAppointments * 100) : 0 ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="status-pill bg-danger-soft">Cancelled</span>
                                <span class="fw-bold"><?= $cancelledAppointments ?></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: <?= ($totalAppointments > 0) ? ($cancelledAppointments / $totalAppointments * 100) : 0 ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Export Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h5 class="section-title">Export Data</h5>
                <div class="card export-card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="export-users.php" class="btn action-btn btn-primary-soft w-100">
                                    <i class="fas fa-users me-2"></i> Export Users
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="export-doctors.php" class="btn action-btn btn-success-soft w-100">
                                    <i class="fas fa-user-md me-2"></i> Export Doctors
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="export-patients.php" class="btn action-btn btn-info-soft w-100">
                                    <i class="fas fa-procedures me-2"></i> Export Patients
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="export-appointments.php" class="btn action-btn btn-warning-soft w-100">
                                    <i class="fas fa-calendar-check me-2"></i> Export Appointments
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>