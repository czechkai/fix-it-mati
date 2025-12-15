<?php
/**
 * Email Configuration
 * Configure SMTP settings for sending verification emails
 * 
 * INSTALLATION:
 * ============
 * To use PHPMailer for robust email sending, install via Composer:
 * 
 *   composer require phpmailer/phpmailer
 * 
 * Or manually download from: https://github.com/PHPMailer/PHPMailer/releases
 */

return [
    // Email from address
    'from_email' => getenv('MAIL_FROM') ?: 'fixitmati@gmail.com',
    'from_name' => 'FixItMati',

    // SMTP Configuration (optional - uses PHP mail() function if not configured)
    'smtp' => [
        'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'port' => (int)(getenv('MAIL_PORT') ?: 587),
        'username' => getenv('MAIL_USERNAME') ?: 'fixitmati@gmail.com',
        'password' => getenv('MAIL_PASSWORD') ?: 'baekzlltzngeggtm',
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls', // 'tls' or 'ssl'
    ],

    /**
     * CONFIGURATION EXAMPLES
     * ======================
     * 
     * 1. MAILTRAP (Development/Testing - Recommended)
     *    Website: https://mailtrap.io
     *    
     *    'smtp' => [
     *        'host' => 'smtp.mailtrap.io',
     *        'port' => 587,
     *        'username' => 'your_inbox_username',
     *        'password' => 'your_inbox_password',
     *        'encryption' => 'tls',
     *    ],
     * 
     * 2. GMAIL
     *    Requirements:
     *    - Enable 2-factor authentication
     *    - Create an "App Password" (not your regular password)
     *    
     *    'smtp' => [
     *        'host' => 'smtp.gmail.com',
     *        'port' => 587,
     *        'username' => 'your.email@gmail.com',
     *        'password' => 'your_app_password',
     *        'encryption' => 'tls',
     *    ],
     * 
     * 3. SENDGRID
     *    Website: https://sendgrid.com
     *    
     *    'smtp' => [
     *        'host' => 'smtp.sendgrid.net',
     *        'port' => 587,
     *        'username' => 'apikey',  // Always 'apikey'
     *        'password' => 'your_sendgrid_api_key',
     *        'encryption' => 'tls',
     *    ],
     * 
     * 4. AWS SES
     *    Region example: us-east-1
     *    
     *    'smtp' => [
     *        'host' => 'email-smtp.us-east-1.amazonaws.com',
     *        'port' => 587,
     *        'username' => 'your_smtp_username',
     *        'password' => 'your_smtp_password',
     *        'encryption' => 'tls',
     *    ],
     * 
     * 5. ENVIRONMENT VARIABLES
     *    Set these in your .env or server environment:
     *    
     *    MAIL_HOST=smtp.mailtrap.io
     *    MAIL_PORT=587
     *    MAIL_USERNAME=your_username
     *    MAIL_PASSWORD=your_password
     *    MAIL_ENCRYPTION=tls
     *    MAIL_FROM=noreply@fixitmati.local
     */
    
    // Fallback: If no SMTP configured, will use PHP mail() function
];

