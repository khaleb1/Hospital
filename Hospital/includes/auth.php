<?php
// Only start session if one doesn't exist already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection
require_once 'db.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user has specific role
function checkRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

// Generate a verification token
function generateVerificationToken() {
    return bin2hex(random_bytes(32));
}

// Send verification email
function sendVerificationEmail($email, $full_name, $token) {
    $verificationUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/Hospital/verify-email.php?token=' . $token;
    $subject = 'Verify Your Email - Hospital Management System';
    $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .button { display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Email Verification</h2>
                </div>
                <div class='content'>
                    <p>Hello {$full_name},</p>
                    <p>Thank you for registering with our Hospital Management System. Please verify your email address by clicking the button below:</p>
                    <p style='text-align: center;'>
                        <a href='{$verificationUrl}' class='button'>Verify Email Address</a>
                    </p>
                    <p>If you did not create an account, please ignore this email.</p>
                    <p>The verification link will expire in 24 hours.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
    ";
    
    require_once 'email_functions.php';
    return sendEmail($email, $subject, $body);
}

// Register a new user
function registerUser($username, $password, $full_name, $email, $phone, $role = 'patient') {
    global $conn;
    
    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'error' => 'Username or email already exists'];
    }
    
    // Generate verification token
    $verification_token = generateVerificationToken();
    $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, phone, role, verification_token, token_expiry, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("ssssssss", $username, $hashed_password, $full_name, $email, $phone, $role, $verification_token, $token_expiry);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        
        // If patient, create patient record
        if ($role === 'patient') {
            $stmt = $conn->prepare("INSERT INTO patients (user_id) VALUES (?)");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
        
        // Send verification email
        if (sendVerificationEmail($email, $full_name, $verification_token)) {
            return ['success' => true, 'user_id' => $user_id];
        } else {
            // If email sending fails, delete the user
            $conn->query("DELETE FROM users WHERE user_id = $user_id");
            return ['success' => false, 'error' => 'Failed to send verification email'];
        }
    } else {
        return ['success' => false, 'error' => 'Registration failed'];
    }
}

// Verify email
function verifyEmail($token) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT user_id, token_expiry FROM users WHERE verification_token = ? AND status = 'pending'");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Check if token is expired
        if (strtotime($user['token_expiry']) < time()) {
            return ['success' => false, 'error' => 'Verification link has expired'];
        }
        
        // Update user status
        $stmt = $conn->prepare("UPDATE users SET status = 'active', verification_token = NULL, token_expiry = NULL WHERE user_id = ?");
        $stmt->bind_param("i", $user['user_id']);
        
        if ($stmt->execute()) {
            return ['success' => true];
        }
    }
    
    return ['success' => false, 'error' => 'Invalid verification token'];
}

// Login user
function loginUser($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT user_id, password, full_name, role, status FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            if ($user['status'] === 'pending') {
                return ['success' => false, 'error' => 'Please verify your email address first'];
            }
            
            if ($user['status'] === 'active') {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                return ['success' => true];
            }
            
            return ['success' => false, 'error' => 'Your account is not active'];
        }
    }
    
    return ['success' => false, 'error' => 'Invalid username or password'];
}

// Logout user
function logoutUser() {
    session_unset();
    session_destroy();
}

// Resend verification email
function resendVerificationEmail($email) {
    global $conn;
    
    // Get user details
    $stmt = $conn->prepare("SELECT user_id, full_name, verification_token, token_expiry, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Only resend if user is pending verification
        if ($user['status'] === 'pending') {
            // Check if token is expired
            if (strtotime($user['token_expiry']) < time()) {
                // Generate new token and expiry
                $new_token = generateVerificationToken();
                $new_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // Update token and expiry
                $stmt = $conn->prepare("UPDATE users SET verification_token = ?, token_expiry = ? WHERE user_id = ?");
                $stmt->bind_param("ssi", $new_token, $new_expiry, $user['user_id']);
                $stmt->execute();
                
                // Send new verification email
                return sendVerificationEmail($email, $user['full_name'], $new_token);
            } else {
                // Resend existing token
                return sendVerificationEmail($email, $user['full_name'], $user['verification_token']);
            }
        }
    }
    
    return false;
}
?>