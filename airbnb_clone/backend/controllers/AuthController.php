<?php
require_once __DIR__ . '/../models/Agent.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/JWTHandler.php';

class AuthController {
    private $db;
    private $agent;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->agent = new Agent($this->db);
    }

    public function login($input) {
        if (empty($input['email']) || empty($input['password'])) {
            Response::error('Email and password are required');
        }

        $this->agent->email = $input['email'];
        $stmt = $this->agent->login();

        if ($stmt->rowCount() === 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($input['password'], $row['password'])) {
                $token = (new JWTHandler())->generateToken([
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'subscription_type' => $row['subscription_type']
                ]);
                Response::success(['token' => $token]);
            } else {
                Response::error('Invalid password');
            }
        } else {
            Response::error('User not found');
        }
    }

    public function register($input) {
        if (empty($input['name']) || empty($input['email']) || empty($input['password'])) {
            Response::error('Name, email, and password are required');
        }

        $this->agent->name = $input['name'];
        $this->agent->email = $input['email'];
        $this->agent->password = $input['password'];

        if ($this->agent->create()) {
            Response::success(null, 'Registration successful');
        } else {
            Response::error('Registration failed');
        }
    }
}
?>
