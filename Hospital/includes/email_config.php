<?php
// Email Configuration
return [
    'provider' => 'smtp', // Options: 'smtp', 'sendgrid', 'mailgun', 'amazon_ses'
    'smtp' => [
        'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'username' => getenv('SMTP_USERNAME') ?: 'your-email@gmail.com',
        'password' => getenv('SMTP_PASSWORD') ?: 'your-app-password',
        'port' => getenv('SMTP_PORT') ?: 587,
        'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls'
    ],
    'sendgrid' => [
        'api_key' => getenv('SENDGRID_API_KEY') ?: 'your-sendgrid-api-key'
    ],
    'mailgun' => [
        'api_key' => getenv('MAILGUN_API_KEY') ?: 'your-mailgun-api-key',
        'domain' => getenv('MAILGUN_DOMAIN') ?: 'your-domain.com'
    ],
    'amazon_ses' => [
        'key' => getenv('AWS_KEY') ?: 'your-aws-key',
        'secret' => getenv('AWS_SECRET') ?: 'your-aws-secret',
        'region' => getenv('AWS_REGION') ?: 'us-east-1'
    ],
    'from' => [
        'email' => getenv('FROM_EMAIL') ?: 'noreply@your-domain.com',
        'name' => getenv('FROM_NAME') ?: 'Hospital Management System'
    ],
    'dkim' => [
        'domain' => getenv('DKIM_DOMAIN') ?: 'your-domain.com',
        'selector' => getenv('DKIM_SELECTOR') ?: 'default',
        'private_key_path' => __DIR__ . '/dkim.private',
        'passphrase' => getenv('DKIM_PASSPHRASE') ?: ''
    ]
]; 