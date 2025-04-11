<?php
// Load all required files
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/utils/Response.php';
require_once __DIR__ . '/utils/JWTHandler.php';
require_once __DIR__ . '/middleware/Middleware.php';
require_once __DIR__ . '/middleware/CorsMiddleware.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/ValidationMiddleware.php';
require_once __DIR__ . '/middleware/RateLimitMiddleware.php';

// Initialize request data
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];
$base_path = '/backend/';
$request_uri = substr($request_uri, strlen($base_path));
$uri_segments = explode('/', trim($request_uri, '/'));
$api_endpoint = $uri_segments[0] ?? '';

// Merge all input data
$input = array_merge(
    json_decode(file_get_contents("php://input"), true) ?? [],
    $_GET,
    $_POST
);

// Define public routes that don't require authentication
$public_routes = [
    'login',
    'register',
    'forgot-password',
    'properties/list',
    'properties/search',
    'properties/view'
];

// Create and process middleware stack
$middlewareStack = new MiddlewareStack([
    'endpoint' => $api_endpoint,
    'method' => $request_method,
    'input' => $input,
    'uri_segments' => $uri_segments
]);

// Add middleware to stack
$middlewareStack
    ->add(new CorsMiddleware())
    ->add(new RateLimitMiddleware(null, 60))
    ->add(new AuthMiddleware(null, $public_routes));

// Process middleware stack
$middlewareStack->process();

// Route API requests
try {
    switch ($api_endpoint) {
        case 'admin':
            require_once __DIR__ . '/api/admin.php';
            break;

        case 'agents':
            require_once __DIR__ . '/api/agents.php';
            break;

        case 'properties':
            require_once __DIR__ . '/api/properties.php';
            break;

        case 'reservations':
            require_once __DIR__ . '/api/reservations.php';
            break;

        case 'payments':
            require_once __DIR__ . '/api/payments.php';
            break;

        case 'users':
            require_once __DIR__ . '/api/users.php';
            break;

        case 'auth':
            require_once __DIR__ . '/api/auth.php';
            break;

        case 'uploads':
            // Handle file uploads and serving
            $file_path = implode('/', array_slice($uri_segments, 1));
            if (file_exists(__DIR__ . '/uploads/' . $file_path)) {
                $mime_type = mime_content_type(__DIR__ . '/uploads/' . $file_path);
                header('Content-Type: ' . $mime_type);
                readfile(__DIR__ . '/uploads/' . $file_path);
                exit();
            }
            Response::notFound('File not found');
            break;

        default:
            if (empty($api_endpoint)) {
                // API root - show status
                Response::success([
                    'status' => 'running',
                    'version' => '1.0.0',
                    'timestamp' => time()
                ]);
            } else {
                Response::notFound('Endpoint not found');
            }
            break;
    }
} catch (Exception $e) {
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        Response::serverError($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        Response::serverError('An internal server error occurred');
    }
}

// Create .htaccess file for URL rewriting
$htaccess_content = "RewriteEngine On\n";
$htaccess_content .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
$htaccess_content .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
$htaccess_content .= "RewriteRule ^(.*)$ index.php/$1 [L,QSA]\n";

file_put_contents(__DIR__ . '/.htaccess', $htaccess_content);

// Create uploads directory if it doesn't exist
if (!file_exists(__DIR__ . '/uploads')) {
    mkdir(__DIR__ . '/uploads', 0755, true);
}

// Create necessary subdirectories in uploads
$upload_dirs = ['properties', 'profiles', 'temp'];
foreach ($upload_dirs as $dir) {
    $dir_path = __DIR__ . '/uploads/' . $dir;
    if (!file_exists($dir_path)) {
        mkdir($dir_path, 0755, true);
    }
}
?>
