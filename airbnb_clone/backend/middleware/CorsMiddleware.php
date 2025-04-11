<?php
require_once __DIR__ . '/Middleware.php';
require_once __DIR__ . '/../config/constants.php';

class CorsMiddleware extends Middleware {
    public function handle($request) {
        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // Check if origin is allowed
            $origin = $_SERVER['HTTP_ORIGIN'];
            if (in_array($origin, ALLOWED_ORIGINS)) {
                header("Access-Control-Allow-Origin: $origin");
            }
        } else {
            header("Access-Control-Allow-Origin: *");
        }

        // Allow credentials
        header("Access-Control-Allow-Credentials: true");

        // Cache preflight response for 1 day
        header("Access-Control-Max-Age: 86400");

        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
            }

            // Stop processing for preflight requests
            exit(0);
        }

        // Set common headers for all responses
        header('Content-Type: application/json; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Set security headers
        if (!empty($_ENV['ENVIRONMENT']) && $_ENV['ENVIRONMENT'] === 'production') {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
            header("Content-Security-Policy: default-src 'self'");
        }

        return $this->handleNext($request);
    }
}
?>
