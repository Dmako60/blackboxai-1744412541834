<?php
// Enable error reporting for development
if (true) { // Change to false in production
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Set timezone
date_default_timezone_set('UTC');

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'airbnb_clone');
define('DB_USER', 'root');
define('DB_PASS', '');

// API configuration
define('JWT_SECRET', 'your_jwt_secret_key_change_this_in_production'); // Change this to a secure secret key
define('JWT_EXPIRATION', 86400); // 24 hours in seconds

// File upload configuration
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif'
]);

// Pagination defaults
define('DEFAULT_PAGE_SIZE', 10);
define('MAX_PAGE_SIZE', 100);

// API Response codes
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_NO_CONTENT', 204);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_SERVER_ERROR', 500);

// Subscription limits
define('BASE_PLAN_LIMIT', 4);
define('GOLD_PLAN_LIMIT', 10);
define('VIP_PLAN_LIMIT', PHP_INT_MAX);

// Payment configuration
define('STRIPE_SECRET_KEY', 'your_stripe_secret_key');
define('STRIPE_PUBLISHABLE_KEY', 'your_stripe_publishable_key');
define('PAYPAL_CLIENT_ID', 'your_paypal_client_id');
define('PAYPAL_CLIENT_SECRET', 'your_paypal_client_secret');

// Email configuration
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_smtp_username');
define('SMTP_PASSWORD', 'your_smtp_password');
define('SMTP_FROM_EMAIL', 'noreply@example.com');
define('SMTP_FROM_NAME', 'Airbnb Clone');

// Base URL for API
define('API_BASE_URL', 'http://localhost/backend/');

// CORS allowed origins
define('ALLOWED_ORIGINS', [
    'http://localhost:3000',
    'http://localhost:8000'
]);

// Session configuration
define('SESSION_LIFETIME', 86400); // 24 hours in seconds
define('SESSION_NAME', 'airbnb_session');

// Security
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds

// Property configuration
define('MAX_PROPERTY_IMAGES', 10);
define('MIN_PRICE_PER_NIGHT', 10);
define('MAX_PRICE_PER_NIGHT', 10000);
define('MAX_GUESTS_PER_PROPERTY', 20);

// Reservation configuration
define('MAX_RESERVATION_DAYS', 30);
define('MIN_RESERVATION_DAYS', 1);
define('CANCELLATION_DEADLINE_HOURS', 48);
define('CANCELLATION_REFUND_PERCENTAGE', 80);

// Cache configuration
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour in seconds

// API Rate limiting
define('RATE_LIMIT_REQUESTS', 100); // requests per window
define('RATE_LIMIT_WINDOW', 3600); // 1 hour in seconds
?>
