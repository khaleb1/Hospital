<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Add debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Debug: Check if email and password are received
    echo "Email: " . htmlspecialchars($email) . "<br>";
    
    // Query to find the admin user by email
    $stmt = $conn->prepare("SELECT user_id, full_name, password, role, status FROM users WHERE email = ? AND role = 'admin'");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    // Debug: Check if user is found
    echo "User found: " . ($result->num_rows > 0 ? "Yes" : "No") . "<br>";
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Debug: Show the stored hashed password
        echo "Stored password hash: " . $user['password'] . "<br>";
        
        // Debug: Check password verification
        echo "Password verification: " . (password_verify($password, $user['password']) ? "Success" : "Failed") . "<br>";
        
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Check if email is verified
            if ($user['status'] === 'pending') {
                $_SESSION['login_error'] = "Please verify your email address before logging in. Check your email for the verification link.";
                header("Location: ../login.php");
                exit();
            }

            if ($user['status'] === 'inactive') {
                $_SESSION['login_error'] = "Your account has been deactivated. Please contact support.";
                header("Location: ../login.php");
                exit();
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            echo "Login successful. Redirecting to dashboard...<br>";
            // Redirect to admin dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid email or password.";
            echo "Password verification failed.<br>";
        }
    } else {
        $_SESSION['login_error'] = "Invalid email or password.";
        echo "User not found or not an admin.<br>";
    }
    
    // Comment out the redirect temporarily for debugging
    // header("Location: ../login.php");
    // exit();
    echo "Login failed. <a href='../login.php'>Return to login page</a>";
    exit();
}
?>