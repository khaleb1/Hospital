<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';

$message = '';
$success = false;

if (isset($_GET['token'])) {
    $result = verifyEmail($_GET['token']);
    
    if ($result['success']) {
        $message = 'Your email has been verified successfully. You can now log in to your account.';
        $success = true;
    } else {
        $message = $result['error'];
    }
} else {
    $message = 'Invalid verification link.';
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header <?php echo $success ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
                    <h4 class="mb-0">Email Verification</h4>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo $message; ?></p>
                    <?php if ($success): ?>
                        <a href="login.php" class="btn btn-primary">Go to Login</a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-primary">Register Again</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 