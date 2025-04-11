<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/JWTHandler.php';
require_once __DIR__ . '/../utils/UploadHandler.php';

class UserController {
    private $db;
    private $user;
    private $jwt;
    private $upload;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->user = new User($this->db);
        $this->jwt = new JWTHandler();
        $this->upload = new UploadHandler();
    }

    public function register($input) {
        try {
            if (empty($input['email']) || empty($input['password']) || empty($input['name'])) {
                throw new Exception('Name, email and password are required');
            }

            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }

            if ($this->user->emailExists($input['email'])) {
                throw new Exception('Email already registered');
            }

            $this->user->name = $input['name'];
            $this->user->email = $input['email'];
            $this->user->password = password_hash($input['password'], PASSWORD_DEFAULT);
            $this->user->phone = $input['phone'] ?? null;
            $this->user->profile_image = $input['profile_image'] ?? null;

            if ($this->user->create()) {
                $token = $this->jwt->generateToken([
                    'id' => $this->user->id,
                    'email' => $this->user->email,
                    'role' => 'user'
                ]);

                Response::success([
                    'token' => $token,
                    'user' => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email
                    ]
                ], 'Registration successful');
            } else {
                throw new Exception('Failed to register user');
            }
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function login($input) {
        try {
            if (empty($input['email']) || empty($input['password'])) {
                throw new Exception('Email and password are required');
            }

            $stmt = $this->user->getByEmail($input['email']);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($input['password'], $user['password'])) {
                throw new Exception('Invalid credentials');
            }

            $token = $this->jwt->generateToken([
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => 'user'
            ]);

            unset($user['password']);
            Response::success([
                'token' => $token,
                'user' => $user
            ], 'Login successful');
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getProfile($input) {
        try {
            $stmt = $this->user->getById($input['user_data']->id);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception('User not found');
            }

            unset($user['password']);
            Response::success($user);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateProfile($input) {
        try {
            $this->user->id = $input['user_data']->id;
            $this->user->name = $input['name'] ?? null;
            $this->user->phone = $input['phone'] ?? null;
            $this->user->profile_image = $input['profile_image'] ?? null;

            if ($this->user->update()) {
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
                throw new Exception('Current and new passwords are required');
            }

            $stmt = $this->user->getById($input['user_data']->id);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($input['current_password'], $user['password'])) {
                throw new Exception('Current password is incorrect');
            }

            $this->user->id = $input['user_data']->id;
            $this->user->password = password_hash($input['new_password'], PASSWORD_DEFAULT);

            if ($this->user->updatePassword()) {
                Response::success(null, 'Password updated successfully');
            } else {
                throw new Exception('Failed to update password');
            }
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function getFavorites($input) {
        $page = isset($input['page']) ? (int)$input['page'] : 1;
        $limit = isset($input['limit']) ? (int)$input['limit'] : DEFAULT_PAGE_SIZE;

        $this->user->id = $input['user_data']->id;
        $stmt = $this->user->getFavoriteProperties($page, $limit);
        $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->user->getTotalFavorites();

        Response::paginated($favorites, $total, $page, $limit);
    }

    public function toggleFavorite($input) {
        try {
            if (empty($input['property_id'])) {
                throw new Exception('Property ID is required');
            }

            $this->user->id = $input['user_data']->id;
            
            if ($this->user->isFavorite($input['property_id'])) {
                $result = $this->user->removeFromFavorites($input['property_id']);
                $message = 'Property removed from favorites';
            } else {
                $result = $this->user->addToFavorites($input['property_id']);
                $message = 'Property added to favorites';
            }

            if ($result) {
                Response::success(null, $message);
            } else {
                throw new Exception('Failed to update favorites');
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

            $stmt = $this->user->getById($input['user_data']->id);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($input['password'], $user['password'])) {
                throw new Exception('Invalid password');
            }

            $this->user->id = $input['user_data']->id;
            if ($this->user->delete()) {
                Response::success(null, 'Account deleted successfully');
            } else {
                throw new Exception('Failed to delete account');
            }
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
}
?>
