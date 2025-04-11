<?php
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../utils/JWTHandler.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/UploadHandler.php';

$user = new UserController();
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
                
                // Register new user
                $user->register($input);
                break;

            case 'login':
                // Login user
                $user->login($input);
                break;

            case 'forgot-password':
                // Handle forgot password request
                if (empty($input['email'])) {
                    Response::error('Email is required');
                }
                // TODO: Implement forgot password functionality
                Response::error('Feature not implemented yet');
                break;

            case 'toggle-favorite':
                // Toggle property favorite status
                $user->toggleFavorite($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    case 'GET':
        switch ($endpoint) {
            case 'profile':
                // Get user profile
                $user->getProfile($input);
                break;

            case 'favorites':
                // Get user's favorite properties
                $user->getFavorites($input);
                break;

            case 'reservations':
                // Get user's reservations
                $user->getReservations($input);
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
                
                // Update user profile
                $user->updateProfile($input);
                break;

            case 'password':
                // Update password
                $user->updatePassword($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    case 'DELETE':
        switch ($endpoint) {
            case 'account':
                // Delete user account
                $user->deleteAccount($input);
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

// Helper functions for input validation
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    // Password must be at least 8 characters long and contain:
    // - At least one uppercase letter
    // - At least one lowercase letter
    // - At least one number
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

function validatePhone($phone) {
    // Basic phone number validation
    // Allows formats: +1234567890, 1234567890, 123-456-7890
    return preg_match('/^\+?\d{1,4}?[-.\s]?\(?\d{1,3}?\)?[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}$/', $phone);
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags($data));
}

function validateImageFile($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }

    if ($file['size'] > $max_size) {
        return false;
    }

    return true;
}
?>
