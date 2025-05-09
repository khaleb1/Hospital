<?php
session_start();
require_once __DIR__ . '/includes/config.php'; // Ensure this path is correct
require_once __DIR__ . '/includes/header.php'; // Update path to header.php

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'doctor':
            header("Location: doctor/dashboard.php");
            break;
        case 'patient':
            header("Location: patient/dashboard.php");
            break;
        default:
            header("Location: index.php");
    }
    exit();
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
        <div class="card" style="background-color: rgba(255, 255, 255, 0.8);">
        <div class="card-header" style="background-color: rgba(13, 110, 253, 0.0);">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <a class="nav-link active text-primary" href="#user-login" data-bs-toggle="tab" style="font-weight: bold; background-color: transparent;">User Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-primary" href="#admin-login" data-bs-toggle="tab" style="font-weight: bold; background-color: transparent;">Admin Login</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body tab-content" style="background-color: rgba(255, 255, 255, 0);">
                    <div class="tab-pane fade show active" id="user-login">
                        <?php if (isset($_SESSION['login_error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['login_error'] ?>
                                <?php 
                                if (strpos($_SESSION['login_error'], 'verify your email') !== false): 
                                    unset($_SESSION['login_error']);
                                ?>
                                    <div class="mt-2">
                                        <a href="resend-verification.php" class="btn btn-outline-primary btn-sm">Resend Verification Email</a>
                                    </div>
                                <?php else: 
                                    unset($_SESSION['login_error']);
                                endif; 
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="login-process.php" method="POST"> <!-- Ensure this file exists -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                        
                        
                    </div>
                    <div class="tab-pane fade" id="admin-login">
                        
                    
                        <form action="admin/admin-login-process.php" method="POST"> <!-- Ensure this path is correct -->
                            <div class="mb-3">
                                <label for="admin-email" class="form-label">Admin Email</label>
                                <input type="email" class="form-control" id="admin-email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="admin-password" class="form-label">Admin Password</label>
                                <input type="password" class="form-control" id="admin-password" name="password" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Admin Login</button>
                            </div>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>