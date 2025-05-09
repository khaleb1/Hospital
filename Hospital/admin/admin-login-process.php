<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Query to find the admin by email
    $stmt = $conn->prepare("SELECT user_id, full_name, password, role, status FROM users WHERE email = ? AND role = 'admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // TEMPORARY: Direct password comparison instead of hash verification
        // WARNING: This is insecure and should only be used for development
        if ($password === $user['password']) {
            // Check if account is active
            if ($user['status'] === 'inactive') {
                $_SESSION['login_error'] = "Your account has been deactivated. Please contact support.";
                header("Location: ../login.php");
                exit();
            }

            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = 'admin';

            // Redirect to admin dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid email or password.";
        }
    } else {
        $_SESSION['login_error'] = "Invalid email or password.";
    }

    // Redirect back to login page with error
    header("Location: ../login.php");
    exit();
}
?>