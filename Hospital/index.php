<?php
require_once './includes/auth.php';
require_once './includes/header.php';

if (isLoggedIn()) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 
                          ($_SESSION['role'] === 'doctor' ? 'doctor/dashboard.php' : 
                          'patient/dashboard.php')));
    exit();
}
?>

<style>
    body {
        background: url('assets/img/hospital-bg.png') no-repeat center center fixed;
        background-size: cover;
        background-color: rgb(187, 215, 243);
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    .hero-section {
        background-color: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(5px);
        min-height: 40vh;
        width: 80%;
        margin: 10vh auto auto auto; /* Adjusted margin to move section up */
        border-radius: 15px;
    }
</style>

<div class="hero-section text-center py-5" style="
    display: flex;
    flex-direction: column;
    justify-content: center;
">
    <h1 class="display-4">Welcome to Hospital Booking System</h1>
    <div class="mt-4">
        <a href="login.php" class="btn btn-primary btn-lg mx-3 px-4 rounded-pill">Login</a>
        <a href="register.php" class="btn btn-success btn-lg mx-3 px-4 rounded-pill">Register as Patient</a>
        <a href="doctor/register.php" class="btn btn-info btn-lg mx-3 px-4 rounded-pill">Register as Doctor</a>
    </div>
</div>

<?php require_once './includes/footer.php'; ?>