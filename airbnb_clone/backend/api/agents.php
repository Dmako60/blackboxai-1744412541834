<?php
require_once __DIR__ . '/../controllers/AgentController.php';
require_once __DIR__ . '/../utils/JWTHandler.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/UploadHandler.php';

$agent = new AgentController();
$jwt = new JWTHandler();
$upload = new UploadHandler();

// Get the endpoint from URI segments
$endpoint = isset($uri_segments[1]) ? $uri_segments[1] : '';

// Public routes that don't require authentication
$public_routes = ['register', 'login', 'forgot-password'];

// Verify token for protected routes
if (!in_array($endpoint, $public_routes)) {
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        Response::unauthorized('Authorization token required');
    }

    try {
        $token_data = $jwt->getTokenData($_SERVER['HTTP_AUTHORIZATION']);
        $input['user_data'] = $token_data;
    } catch (Exception $e) {
        Response::unauthorized('Invalid token');
    }
}

// Route the request based on the endpoint and method
switch ($request_method) {
    case 'POST':
        switch ($endpoint) {
            case 'register':
                // Handle profile image upload
                if (isset($_FILES['profile_image'])) {
                    $result = $upload->uploadSingle($_FILES['profile_image'], 'profiles');
                    if ($result) {
                        $input['profile_image'] = $result;
                    }
                }
                
                // Register new agent
                $agent->register($input);
                break;

            case 'login':
                // Login agent
                if (empty($input['email']) || empty($input['password'])) {
                    Response::error('Email and password are required');
                }
                $agent->login($input);
                break;

            case 'forgot-password':
                // Handle forgot password request
                if (empty($input['email'])) {
                    Response::error('Email is required');
                }
                $agent->forgotPassword($input);
                break;

            case 'reset-password':
                // Reset password with token
                if (empty($input['token']) || empty($input['password'])) {
                    Response::error('Token and new password are required');
                }
                $agent->resetPassword($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    case 'GET':
        switch ($endpoint) {
            case 'profile':
                // Get agent profile
                $agent->getProfile($input);
                break;

            case 'properties':
                // Get agent's properties
                $agent->getProperties($input);
                break;

            case 'subscription':
                // Get agent's subscription details
                $agent->getSubscription($input);
                break;

            case 'earnings':
                // Get agent's earnings
                $agent->getEarnings($input);
                break;

            case 'reservations':
                // Get reservations for agent's properties
                $agent->getReservations($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    case 'PUT':
        switch ($endpoint) {
            case 'profile':
                // Handle profile image update
                if (isset($_FILES['profile_image'])) {
                    $result = $upload->uploadSingle($_FILES['profile_image'], 'profiles');
                    if ($result) {
                        $input['profile_image'] = $result;
                    }
                }
                
                // Update agent profile
                $agent->updateProfile($input);
                break;

            case 'password':
                // Update password
                if (empty($input['current_password']) || empty($input['new_password'])) {
                    Response::error('Current password and new password are required');
                }
                $agent->updatePassword($input);
                break;

            case 'subscription':
                // Update subscription
                if (empty($input['subscription_type'])) {
                    Response::error('Subscription type is required');
                }
                $agent->updateSubscription($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    case 'DELETE':
        switch ($endpoint) {
            case 'account':
                // Delete agent account
                if (empty($input['password'])) {
                    Response::error('Password is required to delete account');
                }
                $agent->deleteAccount($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    default:
        Response::error('Method not allowed', 405);
        break;
}

// Helper function to validate subscription type
function isValidSubscriptionType($type) {
    return in_array($type, ['base', 'gold', 'vip']);
}

// Helper function to validate password
function isValidPassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

// Helper function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
?>
