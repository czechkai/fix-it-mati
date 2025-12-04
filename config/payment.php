<?php
/**
 * Payment Gateway Configuration
 * 
 * Store API credentials in environment variables for security
 * Never commit real credentials to version control
 */

return [
    'stripe' => [
        'api_key' => $_ENV['STRIPE_SECRET_KEY'] ?? '',
        'publishable_key' => $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '',
        'webhook_secret' => $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '',
        'api_version' => '2023-10-16',
        'currency' => 'php',
        'enabled' => filter_var($_ENV['STRIPE_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
        'mode' => $_ENV['STRIPE_MODE'] ?? 'test', // 'test' or 'live'
    ],
    
    'paypal' => [
        'client_id' => $_ENV['PAYPAL_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['PAYPAL_CLIENT_SECRET'] ?? '',
        'webhook_id' => $_ENV['PAYPAL_WEBHOOK_ID'] ?? '',
        'mode' => $_ENV['PAYPAL_MODE'] ?? 'sandbox', // 'sandbox' or 'live'
        'currency' => 'PHP',
        'enabled' => filter_var($_ENV['PAYPAL_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    ],
    
    'gcash' => [
        'merchant_id' => $_ENV['GCASH_MERCHANT_ID'] ?? '',
        'api_key' => $_ENV['GCASH_API_KEY'] ?? '',
        'api_secret' => $_ENV['GCASH_API_SECRET'] ?? '',
        'webhook_secret' => $_ENV['GCASH_WEBHOOK_SECRET'] ?? '',
        'api_url' => $_ENV['GCASH_API_URL'] ?? 'https://api.gcash.com/v1',
        'enabled' => filter_var($_ENV['GCASH_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    ],
    
    // PayMongo - Recommended for Philippines (handles GCash, GrabPay, Cards, etc.)
    'paymongo' => [
        'public_key' => $_ENV['PAYMONGO_PUBLIC_KEY'] ?? '',
        'secret_key' => $_ENV['PAYMONGO_SECRET_KEY'] ?? '',
        'webhook_secret' => $_ENV['PAYMONGO_WEBHOOK_SECRET'] ?? '',
        'enabled' => filter_var($_ENV['PAYMONGO_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    ],
    
    // Xendit - Alternative for Philippines (handles GCash, PayMaya, Cards, etc.)
    'xendit' => [
        'secret_key' => $_ENV['XENDIT_SECRET_KEY'] ?? '',
        'public_key' => $_ENV['XENDIT_PUBLIC_KEY'] ?? '',
        'webhook_token' => $_ENV['XENDIT_WEBHOOK_TOKEN'] ?? '',
        'enabled' => filter_var($_ENV['XENDIT_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    ],
    
    // General payment settings
    'default_gateway' => $_ENV['DEFAULT_PAYMENT_GATEWAY'] ?? 'stripe',
    'retry_attempts' => (int) ($_ENV['PAYMENT_RETRY_ATTEMPTS'] ?? 3),
    'retry_delay' => (int) ($_ENV['PAYMENT_RETRY_DELAY'] ?? 2), // seconds
    'timeout' => (int) ($_ENV['PAYMENT_TIMEOUT'] ?? 30), // seconds
    
    // Logging
    'log_transactions' => filter_var($_ENV['LOG_PAYMENT_TRANSACTIONS'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
    'log_level' => $_ENV['PAYMENT_LOG_LEVEL'] ?? 'info', // debug, info, warning, error
];
