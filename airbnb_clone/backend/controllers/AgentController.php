<?php
require_once __DIR__ . '/../models/Agent.php';
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/JWTHandler.php';
require_once __DIR__ . '/../utils/UploadHandler.php';

class AgentController {
    private $db;
    private $agent;
    private $property;
    private $jwt;
    private $upload;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->agent = new Agent($this->db);
        $this->property = new Property($this->db);
        $this->jwt = new JWTHandler();
        $this->upload = new UploadHandler();
    }

    public function register($input) {
        try {
            // Validate required fields
            $required_fields = ['name', 'email', 'password', 'phone'];
            foreach ($required_fields as $field) {
                if (empty($input[$field])) {
                    throw new Exception("$field is required");
                }
            }

            // Validate email format
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }

            // Check if email already exists
            if ($this->agent->emailExists($input['email'])) {
                throw new Exception('Email already registered');
            }

            // Hash password
            $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT);

            // Set agent data
            $this->agent->name = $input['name'];
            $this->agent->email = $input['email'];
            $this->agent->password = $hashed_password;
            $this->agent->phone = $input['phone'];
            $this->agent->address = $input['address'] ?? null;
            $this->agent->status = 'pending';
            $this->agent->subscription_type = 'base';

            // Handle profile image upload
            if (isset($input['profile_image'])) {
                $this->agent->profile_image = $input['profile_image'];
            }

            // Create agent
            if ($this->agent->create()) {
                Response::success([
                    'agent_id' => $this->agent->id
                ], 'Registration successful. Please wait for admin approval.');
            } else {
                throw new Exception('Failed to register agent');
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function login($input) {
        try {
            // Validate input
            if (empty($input['email']) || empty($input['password'])) {
                throw new Exception('Email and password are required');
            }

            // Get agent by email
            $stmt = $this->agent->getByEmail($input['email']);
            $agent = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$agent) {
                throw new Exception('Invalid credentials');
            }

            // Verify password
            if (!password_verify($input['password'], $agent['password'])) {
                throw new Exception('Invalid credentials');
            }

            // Check if agent is approved
            if ($agent['status'] !== 'approved') {
                throw new Exception('Your account is pending approval');
            }

            // Generate JWT token
            $token = $this->jwt->generateToken([
                'id' => $agent['id'],
                'email' => $agent['email'],
                'role' => 'agent'
            ]);

            // Return agent data and token
            unset($agent['password']);
            Response::success([
                'token' => $token,
                'agent' => $agent
            ], 'Login successful');

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getProfile($input) {
        try {
            $stmt = $this->agent->getById($input['user_data']->id);
            $agent = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$agent) {
                throw new Exception('Agent not found');
            }

            // Remove sensitive data
            unset($agent['password']);

            Response::success($agent);

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateProfile($input) {
        try {
            $this->agent->id = $input['user_data']->id;

            // Update basic info
            if (isset($input['name'])) $this->agent->name = $input['name'];
            if (isset($input['phone'])) $this->agent->phone = $input['phone'];
            if (isset($input['address'])) $this->agent->address = $input['address'];

            // Handle profile image update
            if (isset($input['profile_image'])) {
                // Delete old image if exists
                $stmt = $this->agent->getById($this->agent->id);
                $current_agent = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($current_agent['profile_image']) {
                    $this->upload->deleteFile($current_agent['profile_image']);
                }
                
                $this->agent->profile_image = $input['profile_image'];
            }

            if ($this->agent->update()) {
                Response::success(null, 'Profile updated successfully');
            } else {
                throw new Exception('Failed to update profile');
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updatePassword($input) {
        try {
            if (empty($input['current_password']) || empty($input['new_password'])) {
                throw new Exception('Current password and new password are required');
            }

            // Get current agent data
            $stmt = $this->agent->getById($input['user_data']->id);
            $agent = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify current password
            if (!password_verify($input['current_password'], $agent['password'])) {
                throw new Exception('Current password is incorrect');
            }

            // Update password
            $this->agent->id = $input['user_data']->id;
            $this->agent->password = password_hash($input['new_password'], PASSWORD_DEFAULT);

            if ($this->agent->updatePassword()) {
                Response::success(null, 'Password updated successfully');
            } else {
                throw new Exception('Failed to update password');
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getProperties($input) {
        $page = isset($input['page']) ? (int)$input['page'] : 1;
        $limit = isset($input['limit']) ? (int)$input['limit'] : DEFAULT_PAGE_SIZE;
        $status = isset($input['status']) ? $input['status'] : null;

        $stmt = $this->property->getAllProperties($page, $limit, $status, $input['user_data']->id);
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->property->getTotalPropertyCount($status, $input['user_data']->id);

        Response::paginated(
            $properties,
            $total,
            $page,
            $limit
        );
    }

    public function updateSubscription($input) {
        try {
            if (empty($input['subscription_type'])) {
                throw new Exception('Subscription type is required');
            }

            if (!in_array($input['subscription_type'], ['base', 'gold', 'vip'])) {
                throw new Exception('Invalid subscription type');
            }

            $this->agent->id = $input['user_data']->id;
            $this->agent->subscription_type = $input['subscription_type'];
            $this->agent->subscription_expiry = date('Y-m-d', strtotime('+1 month'));

            if ($this->agent->updateSubscription()) {
                Response::success(null, 'Subscription updated successfully');
            } else {
                throw new Exception('Failed to update subscription');
            }

        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function deleteAccount($input) {
        try {
            if (empty($input['password'])) {
                throw new Exception('Password is required to delete account');
            }

            // Verify password
            $stmt = $this->agent->getById($input['user_data']->id);
            $agent = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($input['password'], $agent['password'])) {
                throw new Exception('Invalid password');
            }

            // Start transaction
            $this->db->beginTransaction();

            // Delete agent's properties
            $stmt = $this->property->getAllProperties(1, PHP_INT_MAX, null, $input['user_data']->id);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($properties as $property) {
                $this->property->id = $property['id'];
                if (!$this->property->delete()) {
                    throw new Exception('Failed to delete agent properties');
                }
            }

            // Delete agent
            $this->agent->id = $input['user_data']->id;
            if (!$this->agent->delete()) {
                throw new Exception('Failed to delete account');
            }

            // Commit transaction
            $this->db->commit();

            Response::success(null, 'Account deleted successfully');

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            Response::error($e->getMessage());
        }
    }
}
?>
