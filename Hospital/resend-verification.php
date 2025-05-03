<?php
require_once './includes/auth.php';
require_once './includes/header.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $message = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format';
    } else {
        if (resendVerificationEmail($email)) {
            $message = 'Verification email has been resent. Please check your inbox.';
            $success = true;
        } else {
            $message = 'Failed to resend verification email. Please make sure you have registered with this email address.';
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header <?php echo $success ? 'bg-success text-white' : 'bg-primary text-white'; ?>">
                    <h4 class="mb-0">Resend Verification Email</h4>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                   required>
                            <div class="form-text">Enter the email address you used to register</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Resend Verification Email</button>
                    </form>
                    
                    <div class="mt-3">
                        <a href="login.php" class="btn btn-outline-secondary">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once './includes/footer.php'; ?> 