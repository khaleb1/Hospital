<?php
$to = "your@email.com"; // Replace with your real email address for testing
$subject = "Test Email from XAMPP Mercury";
$message = "This is a test email sent from PHP using Mercury Mail on XAMPP.";
$headers = "From: noreply@localhost";

if (mail($to, $subject, $message, $headers)) {
    echo "Test email sent successfully!";
} else {
    echo "Failed to send test email.";
}
?>