<?php
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../models/Agent.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/UploadHandler.php';

class PropertyController {
    private $db;
    private $property;
    private $agent;
    private $upload;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->property = new Property($this->db);
        $this->agent = new Agent($this->db);
        $this->upload = new UploadHandler();
    }

    public function add($input) {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Check if agent can add more properties
            $this->agent->id = $input['user_data']->id;
            if (!$this->agent->canAddProperty()) {
                throw new Exception('You have reached your property limit. Please upgrade your subscription.');
            }

            // Validate required fields
            $required_fields = ['title', 'description', 'price_per_night', 'location', 'max_guests', 'bedrooms', 'bathrooms'];
            foreach ($required_fields as $field) {
                if (empty($input[$field])) {
                    throw new Exception("$field is required");
                }
            }

            // Set property data
            $this->property->agent_id = $input['user_data']->id;
            $this->property->title = $input['title'];
            $this->property->description = $input['description'];
            $this->property->price_per_night = $input['price_per_night'];
            $this->property->location = $input['location'];
            $this->property->latitude = $input['latitude'] ?? null;
            $this->property->longitude = $input['longitude'] ?? null;
            $this->property->amenities = json_encode($input['amenities'] ?? []);
            $this->property->rules = $input['rules'] ?? null;
            $this->property->max_guests = $input['max_guests'];
            $this->property->bedrooms = $input['bedrooms'];
            $this->property->bathrooms = $input['bathrooms'];
            $this->property->status = 'pending';
            $this->property->youtube_url = $input['youtube_url'] ?? null;

            // Create property
            if (!$this->property->create()) {
                throw new Exception('Failed to create property');
            }

            // Handle image uploads
            if (isset($input['images']) && is_array($input['images'])) {
                foreach ($input['images'] as $image) {
                    $this->property->addImage($image);
                }
            }

            // Increment agent's property count
            if (!$this->agent->incrementPropertyCount()) {
                throw new Exception('Failed to update agent property count');
            }

            // Commit transaction
            $this->db->commit();

            Response::success([
                'property_id' => $this->property->id
            ], 'Property created successfully');

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            Response::error($e->getMessage());
        }
    }

    public function update($input) {
        try {
            // Validate property ownership
            $stmt = $this->property->getById($input['id']);
            $property = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$property) {
                throw new Exception('Property not found');
            }

            if ($property['agent_id'] != $input['user_data']->id && !$input['user_data']->is_admin) {
                throw new Exception('You do not have permission to update this property');
            }

            // Update property data
            $this->property->id = $input['id'];
            $this->property->title = $input['title'] ?? $property['title'];
            $this->property->description = $input['description'] ?? $property['description'];
            $this->property->price_per_night = $input['price_per_night'] ?? $property['price_per_night'];
            $this->property->location = $input['location'] ?? $property['location'];
            $this->property->latitude = $input['latitude'] ?? $property['latitude'];
            $this->property->longitude = $input['longitude'] ?? $property['longitude'];
            $this->property->amenities = isset($input['amenities']) ? json_encode($input['amenities']) : $property['amenities'];
            $this->property->rules = $input['rules'] ?? $property['rules'];
            $this->property->max_guests = $input['max_guests'] ?? $property['max_guests'];
            $this->property->bedrooms = $input['bedrooms'] ?? $property['bedrooms'];
            $this->property->bathrooms = $input['bathrooms'] ?? $property['bathrooms'];
            $this->property->youtube_url = $input['youtube_url'] ?? $property['youtube_url'];

            if (!$this->property->update()) {
                throw new Exception('Failed to update property');
            }

            // Handle new image uploads
            if (isset($input['new_images']) && is_array($input['new_images'])) {
                foreach ($input['new_images'] as $image) {
                    $this->property->addImage($image);
                }
            }

            Response::success(null, 'Property updated successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function delete($input) {
        try {
            // Validate property ownership
            $stmt = $this->property->getById($input['id']);
            $property = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$property) {
                throw new Exception('Property not found');
            }

            if ($property['agent_id'] != $input['user_data']->id && !$input['user_data']->is_admin) {
                throw new Exception('You do not have permission to delete this property');
            }

            // Start transaction
            $this->db->beginTransaction();

            // Delete property images
            $this->property->id = $input['id'];
            $images = $this->property->getImages();
            foreach ($images as $image) {
                $this->upload->deleteFile($image['image_url']);
            }

            // Delete property
            if (!$this->property->delete()) {
                throw new Exception('Failed to delete property');
            }

            // Decrement agent's property count
            $this->agent->id = $property['agent_id'];
            if (!$this->agent->decrementPropertyCount()) {
                throw new Exception('Failed to update agent property count');
            }

            // Commit transaction
            $this->db->commit();

            Response::success(null, 'Property deleted successfully');

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            Response::error($e->getMessage());
        }
    }

    public function list($input) {
        $page = isset($input['page']) ? (int)$input['page'] : 1;
        $limit = isset($input['limit']) ? (int)$input['limit'] : DEFAULT_PAGE_SIZE;
        $status = isset($input['status']) ? $input['status'] : 'active';
        $agent_id = isset($input['agent_id']) ? (int)$input['agent_id'] : null;

        $stmt = $this->property->getAllProperties($page, $limit, $status, $agent_id);
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->property->getTotalPropertyCount($status, $agent_id);

        Response::paginated(
            $properties,
            $total,
            $page,
            $limit
        );
    }

    public function view($input) {
        $stmt = $this->property->getById($input['id']);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$property) {
            Response::notFound('Property not found');
        }

        Response::success($property);
    }

    public function search($input) {
        $page = isset($input['page']) ? (int)$input['page'] : 1;
        $limit = isset($input['limit']) ? (int)$input['limit'] : DEFAULT_PAGE_SIZE;

        $stmt = $this->property->search(
            $input['keywords'],
            $input['location'] ?? null,
            $input['min_price'] ?? null,
            $input['max_price'] ?? null,
            $input['guests'] ?? null,
            $page,
            $limit
        );

        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->property->getTotalSearchResults(
            $input['keywords'],
            $input['location'] ?? null,
            $input['min_price'] ?? null,
            $input['max_price'] ?? null,
            $input['guests'] ?? null
        );

        Response::paginated(
            $properties,
            $total,
            $page,
            $limit
        );
    }

    public function updateStatus($input) {
        try {
            if (!$input['user_data']->is_admin) {
                throw new Exception('Only administrators can update property status');
            }

            $this->property->id = $input['property_id'];
            $this->property->status = $input['status'];

            if (!$this->property->updateStatus()) {
                throw new Exception('Failed to update property status');
            }

            Response::success(null, 'Property status updated successfully');

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
}
?>
