<?php
require_once 'includes/db.php';

try {
    // Check if verification_token column exists
    $check_token = $conn->query("SHOW COLUMNS FROM users LIKE 'verification_token'");
    if ($check_token->num_rows == 0) {
        $sql = "ALTER TABLE users ADD COLUMN verification_token VARCHAR(64) DEFAULT NULL";
        if ($conn->query($sql)) {
            echo "Successfully added verification_token column.\n";
        } else {
            echo "Error adding verification_token column: " . $conn->error . "\n";
        }
    }

    // Check if token_expiry column exists
    $check_expiry = $conn->query("SHOW COLUMNS FROM users LIKE 'token_expiry'");
    if ($check_expiry->num_rows == 0) {
        $sql = "ALTER TABLE users ADD COLUMN token_expiry DATETIME DEFAULT NULL";
        if ($conn->query($sql)) {
            echo "Successfully added token_expiry column.\n";
        } else {
            echo "Error adding token_expiry column: " . $conn->error . "\n";
        }
    }

    // Check if status column exists and has the correct ENUM values
    $check_status = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
    if ($check_status->num_rows > 0) {
        // Update existing users to active status if they don't have a status
        $update_sql = "UPDATE users SET status = 'active' WHERE status IS NULL OR status = ''";
        if ($conn->query($update_sql)) {
            echo "Successfully updated existing users to active status.\n";
        } else {
            echo "Error updating existing users: " . $conn->error . "\n";
        }
    } else {
        // Add status column if it doesn't exist
        $sql = "ALTER TABLE users ADD COLUMN status ENUM('pending', 'active', 'inactive') DEFAULT 'pending' AFTER role";
        if ($conn->query($sql)) {
            echo "Successfully added status column.\n";
            // Update existing users to active status
            $update_sql = "UPDATE users SET status = 'active' WHERE status IS NULL";
            if ($conn->query($update_sql)) {
                echo "Successfully updated existing users to active status.\n";
            } else {
                echo "Error updating existing users: " . $conn->error . "\n";
            }
        } else {
            echo "Error adding status column: " . $conn->error . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?> 