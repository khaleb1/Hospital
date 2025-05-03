<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/db.php';

if (!checkRole('doctor')) {
    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$doctorId = $_SESSION['doctor_id'];

// Get current doctor profile
$doctorQuery = $conn->query("
    SELECT d.*, u.full_name, u.email, u.phone 
    FROM doctors d
    JOIN users u ON d.user_id = u.user_id
    WHERE d.doctor_id = $doctorId
");
$doctor = $doctorQuery->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $specialization = $conn->real_escape_string($_POST['specialization']);
    $qualifications = $conn->real_escape_string($_POST['qualifications']);
    
    // Update user info
    $conn->query("UPDATE users SET 
        full_name = '$fullName',
        email = '$email',
        phone = '$phone'
        WHERE user_id = $userId");
    
    // Update doctor info
    $conn->query("UPDATE doctors SET 
        specialization = '$specialization',
        qualifications = '$qualifications'
        WHERE doctor_id = $doctorId");
    
    $_SESSION['success'] = "Profile updated successfully!";
    header("Location: profile.php");
    exit();
}
?>

<div class="card" style="background-color: rgba(255, 255, 255, 0.8);">
    <div class="card-header" style="background-color: rgba(13, 110, 253, 0.0);">
        <h5 class="mb-0 text-primary">Update Profile</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($doctor['full_name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($doctor['email']) ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($doctor['phone']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Specialization</label>
                    <input type="text" name="specialization" class="form-control" 
                        value="<?= htmlspecialchars($doctor['specialization'] ?? '') ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Qualifications</label>
                <textarea name="qualifications" class="form-control" rows="3"><?= 
                    htmlspecialchars($doctor['qualifications'] ?? '') 
                ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>