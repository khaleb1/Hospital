<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/auth.php'; // Includes session_start() and db connection
require_once '../includes/header.php';

if (!checkRole('patient')) {
    header("Location: ../index.php");
    exit();
}

// --- Get Patient ID ---
$userId = $_SESSION['user_id']; // Assuming user_id is stored in session
$patientQuery = $conn->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
$patientQuery->bind_param("i", $userId);
$patientQuery->execute();
$patientResult = $patientQuery->get_result();
if ($patientResult->num_rows === 0) {
    // Handle error: Patient record not found for the logged-in user
    die("Error: Patient record not found."); // Or redirect with an error message
}
$patientData = $patientResult->fetch_assoc();
$patientId = $patientData['patient_id'];
$patientQuery->close();
// --- End Get Patient ID ---

// --- Get Doctor ID from URL ---
$doctorId = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : null;

if (!$doctorId) {
    // Redirect or show error if no doctor ID is provided
    // For example, redirect back to a doctor selection page
    header("Location: select_doctor.php"); // Assuming you have a page like this
    exit();
}
// --- End Get Doctor ID ---

// --- Process Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get doctor_id from the hidden input now
    $postedDoctorId = $_POST['doctor_id'] ?? null;
    $appointmentDate = $_POST['appointment_date'] ?? null;
    $appointmentTime = $_POST['appointment_time'] ?? null;
    $reason = $_POST['reason'] ?? null;

    // Validate inputs
    if (empty($postedDoctorId) || empty($appointmentDate) || empty($appointmentTime) || empty($reason)) {
        $error = "All fields are required.";
    } elseif ($postedDoctorId != $doctorId) {
        // Security check: Ensure the submitted doctor ID matches the one from the URL
        $error = "Invalid doctor selection.";
    } elseif (strlen($reason) > 500) {
        $error = "Reason must be less than 500 characters";
    } elseif (!preg_match("/^[a-zA-Z0-9 ,.'-]+$/", $reason)) {
        $error = "Invalid characters in reason field";
    }

    if (!isset($error)) {
        // Free any previous results (Good practice, though less likely needed now)
        while ($conn->more_results()) {
            $conn->next_result();
            if ($res = $conn->store_result()) {
                $res->free();
            }
        }

        // Check appointment availability (using prepared statements)
        $checkQuery = $conn->prepare("SELECT appointment_id FROM appointments
                                    WHERE doctor_id = ?
                                    AND appointment_date = ?
                                    AND appointment_time = ?
                                    AND status != 'cancelled'");
        if (!$checkQuery) {
             $error = "Database error (prepare check): " . $conn->error;
        } else {
            $checkQuery->bind_param("iss", $postedDoctorId, $appointmentDate, $appointmentTime);
            $checkQuery->execute();
            $checkResult = $checkQuery->get_result();

            if ($checkResult->num_rows > 0) {
                $error = "This appointment slot is already booked. Please select another time.";
            } else {
                // Insert the appointment (using prepared statements)
                $insertQuery = $conn->prepare("INSERT INTO appointments
                                             (patient_id, doctor_id, appointment_date, appointment_time, reason, status)
                                             VALUES (?, ?, ?, ?, ?, 'scheduled')");
                 if (!$insertQuery) {
                     $error = "Database error (prepare insert): " . $conn->error;
                 } else {
                    $insertQuery->bind_param("iisss", $patientId, $postedDoctorId, $appointmentDate, $appointmentTime, $reason);

                    if ($insertQuery->execute()) {
                        $_SESSION['success_message'] = "Appointment booked successfully!";
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error = "Error booking appointment: " . $insertQuery->error;
                    }
                    $insertQuery->close();
                 }
            }
            $checkQuery->close();
        }
    }
}
// --- End Process Form Submission ---


// --- Get Selected Doctor Details ---
// Use prepared statement for security
$doctorQuery = $conn->prepare("
    SELECT u.full_name, d.specialization
    FROM doctors d
    JOIN users u ON d.user_id = u.user_id
    WHERE d.doctor_id = ? AND u.status = 'active'
");
$doctorQuery->bind_param("i", $doctorId);
$doctorQuery->execute();
$doctorResult = $doctorQuery->get_result();

if ($doctorResult->num_rows === 0) {
    // Handle error: Doctor not found or not active
    die("Error: Selected doctor not found or is inactive."); // Or redirect
}
$selectedDoctor = $doctorResult->fetch_assoc();
$doctorQuery->close();
// --- End Get Selected Doctor Details ---

// Note: $availableSlots is fetched dynamically via JavaScript now, no need to fetch here.

?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card" style="background-color: rgba(255, 255, 255, 0.8);">
                <div class="card-header" style="background-color: rgba(13, 110, 253, 0.0);">
                    <h5 class="mb-0 text-primary">Book an Appointment with Dr. <?= htmlspecialchars($selectedDoctor['full_name']) ?></h5>
                    <small class="text-muted"><?= htmlspecialchars($selectedDoctor['specialization']) ?></small>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" action="book-appointment.php?doctor_id=<?= $doctorId ?>">
                        <!-- Hidden input to send doctor_id with the form -->
                        <input type="hidden" id="doctor_id_hidden" name="doctor_id" value="<?= $doctorId ?>">

                        <!-- Display selected doctor info instead of dropdown -->
                        <div class="mb-3">
                            <h6>Doctor:</h6>
                            <p><strong><?= htmlspecialchars($selectedDoctor['full_name']) ?></strong> (<?= htmlspecialchars($selectedDoctor['specialization']) ?>)</p>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Appointment Date</label>
                                <select class="form-select" id="appointment_date" name="appointment_date" required disabled>
                                    <option value="">-- Loading Dates --</option>
                                    <!-- Dates will be populated via JavaScript -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Appointment Time</label>
                                <select class="form-select" id="appointment_time" name="appointment_time" required disabled>
                                    <option value="">-- Select Date First --</option>
                                    <!-- Times will be populated via JavaScript -->
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason for Visit</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required><?= isset($_POST['reason']) ? htmlspecialchars($_POST['reason']) : '' ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" class="btn btn-primary">Confirm Booking</button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateSelect = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    const doctorIdInput = document.getElementById('doctor_id_hidden');
    const doctorId = doctorIdInput ? doctorIdInput.value : null;

    if (!dateSelect || !timeSelect || !doctorId) {
        console.error('Form elements or Doctor ID not found');
        return;
    }

    // Function to load available dates
    function loadAvailableDates(doctorId) {
        dateSelect.innerHTML = '<option value="">-- Loading Dates --</option>';
        timeSelect.innerHTML = '<option value="">-- Select Date First --</option>';
        dateSelect.disabled = true;
        timeSelect.disabled = true;

        fetch(`get-doctor-availability.php?doctor_id=${doctorId}`)
            .then(response => {
                if (!response.ok) throw new Error(`Network response was not ok (${response.status})`);
                return response.json();
            })
            .then(dates => {
                console.log('Available dates:', dates);
                dateSelect.innerHTML = '<option value="">-- Select Date --</option>';

                if (!Array.isArray(dates) || dates.length === 0) {
                    dateSelect.innerHTML = '<option value="">-- No Dates Available --</option>';
                    return;
                }

                dates.sort(); // Ensure dates are sorted chronologically

                dates.forEach(date => {
                    if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
                        console.warn(`Invalid date format received: ${date}`);
                        return;
                    }
                    const option = new Option(
                        new Date(date + 'T00:00:00').toLocaleDateString('en-US', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        }),
                        date
                    );
                    dateSelect.add(option);
                });
                dateSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading dates:', error);
                dateSelect.innerHTML = '<option value="">-- Error Loading Dates --</option>';
            });
    }

    // Function to load available time slots for a selected date
    function loadAvailableTimes(doctorId, selectedDate) {
        timeSelect.innerHTML = '<option value="">-- Loading Times --</option>';
        timeSelect.disabled = true;

        fetch(`get-doctor-availability.php?doctor_id=${doctorId}&available_date=${selectedDate}`)
            .then(response => {
                if (!response.ok) throw new Error(`Network response was not ok (${response.status})`);
                return response.json();
            })
            .then(slots => {
                console.log('Available slots:', slots);
                timeSelect.innerHTML = '<option value="">-- Select Time --</option>';

                if (!Array.isArray(slots) || slots.length === 0) {
                    timeSelect.innerHTML = '<option value="">-- No Times Available --</option>';
                    return;
                }

                slots.forEach(slot => {
                    const option = new Option(
                        `${slot.start} - ${slot.end}`,
                        slot.id
                    );
                    timeSelect.add(option);
                });
                timeSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading times:', error);
                timeSelect.innerHTML = '<option value="">-- Error Loading Times --</option>';
            });
    }

    // Event listener for date selection
    dateSelect.addEventListener('change', function() {
        const selectedDate = this.value;
        if (selectedDate) {
            loadAvailableTimes(doctorId, selectedDate);
        } else {
            timeSelect.innerHTML = '<option value="">-- Select Date First --</option>';
            timeSelect.disabled = true;
        }
    });

    // Initial load of available dates
    loadAvailableDates(doctorId);
});
</script>

