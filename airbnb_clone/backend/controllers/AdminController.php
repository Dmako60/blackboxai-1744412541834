<?php
require_once __DIR__ . '/../models/Agent.php';
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/JWTHandler.php';

class AdminController {
    private $db;
    private $agent;
    private $property;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->agent = new Agent($this->db);
        $this->property = new Property($this->db);
    }

    public function login($input) {
        if (empty($input['email']) || empty($input['password'])) {
            Response::error('Email and password are required');
        }

        // Replace with actual admin authentication logic
        if ($input['email'] === 'admin@example.com' && password_verify($input['password'], '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')) {
            $token = (new JWTHandler())->generateToken(['is_admin' => true]);
            Response::success(['token' => $token]);
        } else {
            Response::error('Invalid credentials', 401);
        }
    }

    public function getAllAgents($input) {
        $page = isset($input['page']) ? (int)$input['page'] : 1;
        $limit = isset($input['limit']) ? (int)$input['limit'] : 10;
        $status = isset($input['status']) ? $input['status'] : null;

        $stmt = $this->agent->getAllAgents($page, $limit, $status);
        $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->agent->getTotalAgentCount($status);

        Response::success([
            'agents' => $agents,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => ceil($total / $limit)
        ]);
    }

    public function approveAgent($input) {
        if (empty($input['agent_id'])) {
            Response::error('Agent ID is required');
        }

        $this->agent->id = $input['agent_id'];
        if ($this->agent->approve()) {
            Response::success(null, 'Agent approved successfully');
        } else {
            Response::error('Failed to approve agent');
        }
    }

    public function deleteAgent($input) {
        if (empty($input['agent_id'])) {
            Response::error('Agent ID is required');
        }

        $this->agent->id = $input['agent_id'];
        if ($this->agent->delete()) {
            Response::success(null, 'Agent deleted successfully');
        } else {
            Response::error('Failed to delete agent');
        }
    }

    public function getAllProperties($input) {
        $page = isset($input['page']) ? (int)$input['page'] : 1;
        $limit = isset($input['limit']) ? (int)$input['limit'] : 10;
        $status = isset($input['status']) ? $input['status'] : null;
        $agent_id = isset($input['agent_id']) ? (int)$input['agent_id'] : null;

        $stmt = $this->property->getAllProperties($page, $limit, $status, $agent_id);
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->property->getTotalPropertyCount($status, $agent_id);

        Response::success([
            'properties' => $properties,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => ceil($total / $limit)
        ]);
    }

    public function updatePropertyStatus($input) {
        if (empty($input['property_id']) || empty($input['status'])) {
            Response::error('Property ID and status are required');
        }

        $this->property->id = $input['property_id'];
        if ($this->property->updateStatus($input['status'])) {
            Response::success(null, 'Property status updated successfully');
        } else {
            Response::error('Failed to update property status');
        }
    }

    public function searchAgents($input) {
        if (empty($input['keywords'])) {
            Response::error('Search keywords are required');
        }

        $stmt = $this->agent->search($input['keywords']);
        $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($agents);
    }

    public function searchProperties($input) {
        if (empty($input['keywords'])) {
            Response::error('Search keywords are required');
        }

        $stmt = $this->property->search($input['keywords']);
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($properties);
    }

    public function getDashboardStats() {
        $stats = [
            'total_agents' => $this->agent->getTotalAgentCount(),
            'pending_agents' => $this->agent->getTotalAgentCount('pending'),
            'total_properties' => $this->property->getTotalPropertyCount(),
            'active_properties' => $this->property->getTotalPropertyCount('active'),
            'pending_properties' => $this->property->getTotalPropertyCount('pending'),
            'total_reservations' => $this->getReservationCount(),
            'total_earnings' => $this->getTotalEarnings()
        ];

        Response::success($stats);
    }

    private function getReservationCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM reservations");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    private function getTotalEarnings() {
        $stmt = $this->db->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
?>
