<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';

// Correct the role check to 'patient'
if (!checkRole('patient')) {
    header("Location: ../index.php");
    exit();
}

// Display success message if set
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
    echo $_SESSION['success_message'];
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['success_message']);
}

// Get patient name
$userId = $_SESSION['user_id'];
$patientNameQuery = $conn->query("SELECT full_name FROM users WHERE user_id = $userId");
$patientName = $patientNameQuery->fetch_assoc()['full_name'];

// Add sticky header wrapper before content
?>
<div class="sticky-top" style="z-index: 1020; background-color: white;">
    <?php require_once '../includes/header.php'; ?>
</div>

<div class="container-fluid" style="padding-top: 20px;">
    <!-- Success message display -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); endif; ?>

    <!-- Welcome Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0 text-primary">Welcome</h5>
        </div>
        <div class="card-body">
            <h3 class="text-primary">Hello, <?= htmlspecialchars($patientName) ?>!</h3>
            <p class="text-muted mb-0">Here's what's happening with your appointments today.</p>
        </div>
    </div>

    <div class="row">
        <!-- System Overview -->
        <div class="col-md-3 mb-4">
            <!-- System Overview Card -->
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 text-primary">System Overview</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Total Doctors
                    $doctors = $conn->query("SELECT COUNT(*) FROM doctors")->fetch_row()[0];
                    // Total Patients
                    $patients = $conn->query("SELECT COUNT(*) FROM patients")->fetch_row()[0];
                    ?>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Doctors
                            <span class="badge bg-primary"><?= $doctors ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Patients
                            <span class="badge bg-success"><?= $patients ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Available Doctors Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0 text-primary">Available Doctors</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div>
                        <?php
                        // Increase the limit to show more doctors but make the section scrollable
                        $availableDoctors = $conn->query("
                            SELECT d.doctor_id, u.full_name, d.specialization 
                            FROM doctors d
                            JOIN users u ON d.user_id = u.user_id
                            WHERE u.status = 'active'
                            ORDER BY u.full_name
                            LIMIT 15
                        ");
                        
                        if ($availableDoctors->num_rows > 0) {
                            echo '<ul class="list-group">';
                            while ($doctor = $availableDoctors->fetch_assoc()) {
                                echo '<li class="list-group-item">';
                                echo '<div class="d-flex flex-column">';
                                echo '<span class="badge bg-info mb-2" style="align-self: flex-start;">' . htmlspecialchars($doctor['specialization']) . '</span>';
                                echo '<div class="d-flex justify-content-between align-items-center">';
                                echo '<span class="fw-bold">' . htmlspecialchars($doctor['full_name']) . '</span>';
                                echo '<a href="book-appointment.php?doctor_id=' . $doctor['doctor_id'] . '" class="btn btn-primary btn-sm">Book Appointment</a>';
                                echo '</div>';
                                echo '</div>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<div class="alert alert-info">No doctors available at the moment.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-md-9">
            <!-- Recent Appointments Card -->
            <div class="card recent-appointments-card"> <!-- Added specific class -->
                <div class="card-header">
                    <h5 class="mb-0 text-primary">Recent Appointments</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div style="overflow-y: auto; flex: 1;">
                        <table class="table table-hover">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th>Doctor</th>
                                    <th>Specialization</th>
                                    <th>Date/Time</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Get the patient ID
                                $userId = $_SESSION['user_id'];
                                $patientQuery = $conn->query("SELECT patient_id FROM patients WHERE user_id = $userId");
                                $patientData = $patientQuery->fetch_assoc();
                                $patientId = $patientData['patient_id'];
                                
                                $query = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, 
                                         u.full_name AS doctor_name, d.specialization
                                         FROM appointments a
                                         JOIN doctors d ON a.doctor_id = d.doctor_id
                                         JOIN users u ON d.user_id = u.user_id
                                         WHERE a.patient_id = $patientId
                                         ORDER BY a.appointment_date DESC, a.appointment_time DESC 
                                         LIMIT 5";
                                
                                $result = $conn->query($query);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                                        <td><?= htmlspecialchars($row['specialization']) ?></td>
                                        <td><?= date('M j, Y h:i A', strtotime($row['appointment_date'].' '.$row['appointment_time'])) ?></td>
                                        <td><?= htmlspecialchars($row['reason']) ?></td>
                                    </tr>
                                    <?php endwhile;
                                } else {
                                    echo '<tr><td colspan="4" class="text-center">No appointments found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-between">
                        <!-- Remove this entire button div -->
                    </div>
                </div>
            </div>
            <!-- Remove this entire button div section -->
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
// Remove all the JavaScript code since we don't need it anymore
</script>