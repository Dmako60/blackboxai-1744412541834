<?php
require_once __DIR__ . '/../controllers/PropertyController.php';
require_once __DIR__ . '/../utils/JWTHandler.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/UploadHandler.php';

$property = new PropertyController();
$jwt = new JWTHandler();
$upload = new UploadHandler();

// Get the endpoint from URI segments
$endpoint = isset($uri_segments[1]) ? $uri_segments[1] : '';

// Verify token for protected routes
if (!in_array($endpoint, ['list', 'search', 'view'])) {
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
    case 'GET':
        switch ($endpoint) {
            case 'list':
                // List properties with optional filters
                $property->list($input);
                break;

            case 'view':
                // View single property details
                if (empty($input['id'])) {
                    Response::error('Property ID is required');
                }
                $property->view($input);
                break;

            case 'search':
                // Search properties
                if (empty($input['keywords'])) {
                    Response::error('Search keywords are required');
                }
                $property->search($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    case 'POST':
        switch ($endpoint) {
            case 'add':
                // Handle property images upload
                if (isset($_FILES['images'])) {
                    $uploaded_images = [];
                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        $file = [
                            'name' => $_FILES['images']['name'][$key],
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $tmp_name,
                            'error' => $_FILES['images']['error'][$key],
                            'size' => $_FILES['images']['size'][$key]
                        ];
                        
                        $result = $upload->uploadSingle($file, 'properties');
                        if ($result) {
                            $uploaded_images[] = $result;
                        }
                    }
                    $input['images'] = $uploaded_images;
                }

                // Add new property
                $property->add($input);
                break;

            case 'upload-images':
                // Handle additional image uploads for existing property
                if (empty($input['property_id'])) {
                    Response::error('Property ID is required');
                }

                if (!isset($_FILES['images'])) {
                    Response::error('No images uploaded');
                }

                $uploaded_images = [];
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    $file = [
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key]
                    ];
                    
                    $result = $upload->uploadSingle($file, 'properties');
                    if ($result) {
                        $uploaded_images[] = $result;
                    }
                }

                $input['images'] = $uploaded_images;
                $property->addImages($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    case 'PUT':
        switch ($endpoint) {
            case 'update':
                // Update property details
                if (empty($input['id'])) {
                    Response::error('Property ID is required');
                }
                $property->update($input);
                break;

            case 'status':
                // Update property status
                if (empty($input['id']) || empty($input['status'])) {
                    Response::error('Property ID and status are required');
                }
                $property->updateStatus($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    case 'DELETE':
        switch ($endpoint) {
            case 'delete':
                // Delete property
                if (empty($input['id'])) {
                    Response::error('Property ID is required');
                }
                $property->delete($input);
                break;

            case 'remove-image':
                // Remove specific property image
                if (empty($input['property_id']) || empty($input['image_id'])) {
                    Response::error('Property ID and image ID are required');
                }
                $property->removeImage($input);
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
?>
