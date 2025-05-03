<?php
/**
 * @var PHPMailer\PHPMailer\PHPMailer
 * @var PHPMailer\PHPMailer\Exception
 * @var PHPMailer\PHPMailer\SMTP
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require __DIR__ . '/../vendor/autoload.php';

// Load email configuration
$emailConfig = require __DIR__ . '/email_config.php';

/**
 * Send an email using PHPMailer
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param array $attachments Array of attachments with 'path' and optional 'name' keys
 * @return bool True if email was sent successfully, false otherwise
 */
function sendEmail($to, $subject, $body, $attachments = []) {
    global $emailConfig;
    /** @var PHPMailer $mail */
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = PHPMailer::DEBUG_OFF;
        $mail->isSMTP();
        
        switch ($emailConfig['provider']) {
            case 'sendgrid':
                $mail->Host = 'smtp.sendgrid.net';
                $mail->SMTPAuth = true;
                $mail->Username = 'apikey';
                $mail->Password = $emailConfig['sendgrid']['api_key'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                break;

            case 'mailgun':
                $mail->Host = 'smtp.mailgun.org';
                $mail->SMTPAuth = true;
                $mail->Username = $emailConfig['mailgun']['domain'];
                $mail->Password = $emailConfig['mailgun']['api_key'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                break;

            case 'amazon_ses':
                $mail->Host = 'email-smtp.' . $emailConfig['amazon_ses']['region'] . '.amazonaws.com';
                $mail->SMTPAuth = true;
                $mail->Username = $emailConfig['amazon_ses']['key'];
                $mail->Password = $emailConfig['amazon_ses']['secret'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                break;

            default:
                $mail->Host = $emailConfig['smtp']['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $emailConfig['smtp']['username'];
                $mail->Password = $emailConfig['smtp']['password'];
                $mail->SMTPSecure = $emailConfig['smtp']['encryption'];
                $mail->Port = $emailConfig['smtp']['port'];
        }

        // Recipients
        $mail->setFrom($emailConfig['from']['email'], $emailConfig['from']['name']);
        $mail->addAddress($to);

        // Attachments
        foreach ($attachments as $attachment) {
            if (isset($attachment['path']) && file_exists($attachment['path'])) {
                $mail->addAttachment(
                    $attachment['path'],
                    $attachment['name'] ?? basename($attachment['path'])
                );
            }
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        // DKIM
        if (file_exists($emailConfig['dkim']['private_key_path'])) {
            $mail->DKIM_domain = $emailConfig['dkim']['domain'];
            $mail->DKIM_private = file_get_contents($emailConfig['dkim']['private_key_path']);
            $mail->DKIM_selector = $emailConfig['dkim']['selector'];
            $mail->DKIM_passphrase = $emailConfig['dkim']['passphrase'];
            $mail->DKIM_identity = $mail->From;
        }

        if ($mail->send()) {
            error_log("Email sent successfully to: $to");
            return true;
        } else {
            error_log("Email sending failed to: $to - " . $mail->ErrorInfo);
            return false;
        }
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Send a welcome email to a new user
 * 
 * @param string $userEmail User's email address
 * @param string $userName User's full name
 * @return bool True if email was sent successfully, false otherwise
 */
function sendWelcomeEmail($userEmail, $userName) {
    $loginUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/Hospital/login.php';
    $subject = 'Welcome to Hospital Management System';
    $body = getWelcomeEmailTemplate($userName, $loginUrl);
    
    return sendEmail($userEmail, $subject, $body);
}

/**
 * Generate the welcome email template
 * 
 * @param string $userName User's full name
 * @param string $loginUrl URL for the login page
 * @return string HTML content for the welcome email
 */
function getWelcomeEmailTemplate($userName, $loginUrl) {
    return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .button { display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to Hospital Management System</h1>
                </div>
                <div class='content'>
                    <p>Hello {$userName},</p>
                    <p>Welcome to our Hospital Management System! We're excited to have you on board.</p>
                    <p>You can now log in to your account using the button below:</p>
                    <p style='text-align: center;'>
                        <a href='{$loginUrl}' class='button'>Login to Your Account</a>
                    </p>
                    <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
    ";
}
<?php
function sendVerificationEmail($email, $username, $token) {
    $to = $email;
    $subject = "Email Verification - Hospital System";
    $message = "Hello $username,\n\n";
    $message .= "Please click this link to verify your email:\n";
    $message .= "http://localhost/Hospital/verify.php?token=$token\n\n";
    $message .= "If you didn't request this, please ignore this email.";
    
    $headers = "From: noreply@localhost\r\n";
    $headers .= "Reply-To: noreply@localhost\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>