<?php
function getWelcomeEmailTemplate($userName, $loginUrl) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                background-color: #007bff;
                color: white;
                padding: 20px;
                text-align: center;
                border-radius: 5px 5px 0 0;
            }
            .content {
                padding: 20px;
                background-color: #f8f9fa;
                border-radius: 0 0 5px 5px;
            }
            .button {
                display: inline-block;
                padding: 10px 20px;
                background-color: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin: 20px 0;
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                font-size: 12px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Welcome to Hospital Management System</h2>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($userName) . ",</p>
                <p>Welcome to our Hospital Management System! We're excited to have you on board.</p>
                <p>Your account has been successfully created. You can now log in to access our services.</p>
                <p style='text-align: center;'>
                    <a href='" . htmlspecialchars($loginUrl) . "' class='button'>Login to Your Account</a>
                </p>
                <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                <p>Best regards,<br>Hospital Management System Team</p>
            </div>
            <div class='footer'>
                <p>This is an automated message, please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>";
}
?> 