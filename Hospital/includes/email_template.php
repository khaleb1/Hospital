<?php
require_once __DIR__ . '/email_functions.php';

/**
 * Send an appointment notification email
 * 
 * @param string $email Recipient email address
 * @param string $type Type of notification ('confirmation', 'reminder', 'cancellation', 'reschedule')
 * @param array $details Appointment details
 * @return bool True if email was sent successfully, false otherwise
 */
function sendAppointmentNotification($email, $type, $details) {
    $subject = '';
    $body = '';
    
    switch ($type) {
        case 'confirmation':
            $subject = "Appointment Confirmation - Hospital System";
            $body = getAppointmentConfirmationTemplate($details);
            break;
            
        case 'reminder':
            $subject = "Appointment Reminder - Hospital System";
            $body = getAppointmentReminderTemplate($details);
            break;
            
        case 'cancellation':
            $subject = "Appointment Cancelled - Hospital System";
            $body = getAppointmentCancellationTemplate($details);
            break;
            
        case 'reschedule':
            $subject = "Appointment Rescheduled - Hospital System";
            $body = getAppointmentRescheduleTemplate($details);
            break;
    }
    
    return sendEmail($email, $subject, $body);
}

/**
 * Generate appointment confirmation email template
 * 
 * @param array $details Appointment details
 * @return string HTML content for the confirmation email
 */
function getAppointmentConfirmationTemplate($details) {
    return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .details { background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Appointment Confirmed</h2>
                </div>
                <div class='content'>
                    <p>Dear {$details['patient_name']},</p>
                    <p>Your appointment has been successfully scheduled.</p>
                    <div class='details'>
                        <p><strong>Doctor:</strong> {$details['doctor']}</p>
                        <p><strong>Date:</strong> {$details['date']}</p>
                        <p><strong>Time:</strong> {$details['time']}</p>
                        <p><strong>Location:</strong> {$details['location'] ?? 'Main Hospital Building'}</p>
                        <p><strong>Reason:</strong> {$details['reason'] ?? 'Not specified'}</p>
                    </div>
                    <p>Please arrive 15 minutes before your scheduled time.</p>
                    <p>If you need to cancel or reschedule, please contact us at least 24 hours in advance.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
    ";
}

/**
 * Generate appointment reminder email template
 * 
 * @param array $details Appointment details
 * @return string HTML content for the reminder email
 */
function getAppointmentReminderTemplate($details) {
    return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2196F3; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .details { background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Appointment Reminder</h2>
                </div>
                <div class='content'>
                    <p>Dear {$details['patient_name']},</p>
                    <p>This is a reminder for your upcoming appointment.</p>
                    <div class='details'>
                        <p><strong>Doctor:</strong> {$details['doctor']}</p>
                        <p><strong>Date:</strong> {$details['date']}</p>
                        <p><strong>Time:</strong> {$details['time']}</p>
                        <p><strong>Location:</strong> {$details['location'] ?? 'Main Hospital Building'}</p>
                    </div>
                    <p>Please remember to bring your insurance card and any relevant medical records.</p>
                    <p>If you need to cancel or reschedule, please contact us as soon as possible.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
    ";
}

/**
 * Generate appointment cancellation email template
 * 
 * @param array $details Appointment details
 * @return string HTML content for the cancellation email
 */
function getAppointmentCancellationTemplate($details) {
    return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #f44336; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .details { background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Appointment Cancelled</h2>
                </div>
                <div class='content'>
                    <p>Dear {$details['patient_name']},</p>
                    <p>Your appointment has been cancelled.</p>
                    <div class='details'>
                        <p><strong>Doctor:</strong> {$details['doctor']}</p>
                        <p><strong>Date:</strong> {$details['date']}</p>
                        <p><strong>Time:</strong> {$details['time']}</p>
                        <p><strong>Reason:</strong> {$details['reason'] ?? 'Not specified'}</p>
                    </div>
                    <p>If you would like to reschedule, please contact our office.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
    ";
}

/**
 * Generate appointment reschedule email template
 * 
 * @param array $details Appointment details
 * @return string HTML content for the reschedule email
 */
function getAppointmentRescheduleTemplate($details) {
    return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #FF9800; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .details { background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Appointment Rescheduled</h2>
                </div>
                <div class='content'>
                    <p>Dear {$details['patient_name']},</p>
                    <p>Your appointment has been rescheduled.</p>
                    <div class='details'>
                        <p><strong>Doctor:</strong> {$details['doctor']}</p>
                        <p><strong>New Date:</strong> {$details['new_date']}</p>
                        <p><strong>New Time:</strong> {$details['new_time']}</p>
                        <p><strong>Previous Date:</strong> {$details['old_date']}</p>
                        <p><strong>Previous Time:</strong> {$details['old_time']}</p>
                        <p><strong>Reason:</strong> {$details['reason'] ?? 'Not specified'}</p>
                    </div>
                    <p>If you need to make any changes, please contact our office.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
    ";
}
?>