<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../utils/JWTHandler.php';
require_once __DIR__ . '/../utils/Response.php';

$admin = new AdminController();
$jwt = new JWTHandler();

// Get the HTTP method and endpoint
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

// Get request data
$data = json_decode(file_get_contents("php://input"), true) ?? [];

// Verify admin token for protected routes
if ($endpoint !== 'login') {
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        Response::error('Authorization token required', 401);
        exit();
    }

    try {
        $token_data = $jwt->getTokenData($_SERVER['HTTP_AUTHORIZATION']);
        if (!isset($token_data->is_admin) || !$token_data->is_admin) {
            Response::error('Unauthorized access', 403);
            exit();
        }
    } catch (Exception $e) {
        Response::error('Invalid token', 401);
        exit();
    }
}

// Route handling
switch ($method) {
    case 'POST':
        switch ($endpoint) {
            case 'login':
                if (empty($data['email']) || empty($data['password'])) {
                    Response::error('Email and password are required');
                    break;
                }
                // Verify admin credentials (you should implement proper authentication)
                if ($data['email'] === 'admin@example.com' && $data['password'] === 'admin123') {
                    $token = $jwt->generateToken(['is_admin' => true, 'email' => $data['email']]);
                    Response::success(['token' => $token]);
                } else {
                    Response::error('Invalid credentials', 401);
                }
                break;

            case 'approve-agent':
                $admin->approveAgent($data);
                break;

            default:
                Response::error('Invalid endpoint', 404);
                break;
        }
        break;

    case 'GET':
        switch ($endpoint) {
            case 'dashboard-stats':
                $admin->getDashboardStats();
                break;

            case 'agents':
                $admin->getAllAgents($_GET);
                break;

            case 'properties':
                $admin->getAllProperties($_GET);
                break;

            case 'search-agents':
                if (empty($_GET['keywords'])) {
                    Response::error('Search keywords are required');
                    break;
                }
                $admin->searchAgents($_GET);
                break;

            case 'search-properties':
                if (empty($_GET['keywords'])) {
                    Response::error('Search keywords are required');
                    break;
                }
                $admin->searchProperties($_GET);
                break;

            default:
                Response::error('Invalid endpoint', 404);
                break;
        }
        break;

    case 'PUT':
        switch ($endpoint) {
            case 'property-status':
                $admin->updatePropertyStatus($data);
                break;

            default:
                Response::error('Invalid endpoint', 404);
                break;
        }
        break;

    case 'DELETE':
        switch ($endpoint) {
            case 'delete-agent':
                $admin->deleteAgent($data);
                break;

            case 'delete-property':
                $admin->deleteProperty($data);
                break;

            default:
                Response::error('Invalid endpoint', 404);
                break;
        }
        break;

    default:
        Response::error('Invalid request method', 405);
        break;
}
?>
