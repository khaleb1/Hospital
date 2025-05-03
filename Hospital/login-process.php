<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to find the user by email
    $stmt = $conn->prepare("SELECT user_id, full_name, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Check if email is verified
            if ($user['status'] === 'pending') {
                $_SESSION['login_error'] = "Please verify your email address before logging in. Check your email for the verification link.";
                header("Location: login.php");
                exit();
            }

            if ($user['status'] === 'inactive') {
                $_SESSION['login_error'] = "Your account has been deactivated. Please contact support.";
                header("Location: login.php");
                exit();
            }

            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            switch ($user['role']) {
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
        } else {
            $_SESSION['login_error'] = "Invalid email or password.";
        }
    } else {
        $_SESSION['login_error'] = "Invalid email or password.";
    }

    // Redirect back to login page with error
    header("Location: login.php");
    exit();
}
?>