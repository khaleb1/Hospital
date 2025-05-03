<?php
require_once './includes/auth.php';
require_once './includes/header.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate full name
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    } elseif (strlen($full_name) < 3) {
        $errors[] = "Full name must be at least 3 characters long";
    }

    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters long";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores";
    }

    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Validate phone
    if (!empty($phone) && !preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
        $errors[] = "Invalid phone number format";
    }

    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }

    // Validate password confirmation
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    if (empty($errors)) {
        $response = registerUser(
            $username,
            $password,
            $full_name,
            $email,
            $phone,
            'patient'
        );
        
        if ($response['success']) {
            $success = true;
        } else {
            $errors[] = $response['error'];
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card" style="background-color: rgba(255, 255, 255, 0.8);">
            <div class="card-header" style="background-color: rgba(13, 110, 253, 0.0);">
                <h4 class="mb-0 text-primary">Patient Registration</h4>
            </div>
            <div class="card-body" style="padding-bottom: 100px;">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        Registration successful! Please check your email for verification.
                        <a href="login.php" class="alert-link">Click here to login</a>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="registrationForm">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" 
                                   value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>" 
                                   required>
                            <div class="form-text">Enter your full legal name</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" 
                                   value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" 
                                   required>
                            <div class="form-text">4-20 characters, letters, numbers, and underscores only</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                                   required>
                            <div class="form-text">We'll send a verification email to this address</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                            <div class="form-text">Optional - 10-15 digits, with or without country code</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                            <div class="form-text">
                                Password must be at least 8 characters long and contain:
                                <ul>
                                    <li>At least one uppercase letter</li>
                                    <li>At least one lowercase letter</li>
                                    <li>At least one number</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    const password = document.querySelector('input[name="password"]').value;
    const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
</script>

<?php require_once './includes/footer.php'; ?>
