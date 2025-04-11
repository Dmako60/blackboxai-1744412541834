<?php
require_once __DIR__ . '/Middleware.php';
require_once __DIR__ . '/../utils/JWTHandler.php';
require_once __DIR__ . '/../utils/Response.php';

class AuthMiddleware extends Middleware {
    private $jwt;
    private $excludedRoutes;

    public function __construct($next = null, $excludedRoutes = []) {
        parent::__construct($next);
        $this->jwt = new JWTHandler();
        $this->excludedRoutes = $excludedRoutes;
    }

    public function handle($request) {
        // Check if route is excluded from authentication
        $currentRoute = $request['endpoint'] ?? '';
        if (in_array($currentRoute, $this->excludedRoutes)) {
            return $this->handleNext($request);
        }

        // Check for authorization header
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            Response::unauthorized('Authorization token required');
        }

        try {
            // Validate token and add user data to request
            $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
            $userData = $this->jwt->getTokenData($token);
            $request['user_data'] = $userData;

            return $this->handleNext($request);
        } catch (Exception $e) {
            Response::unauthorized('Invalid token: ' . $e->getMessage());
        }
    }
}
?>
