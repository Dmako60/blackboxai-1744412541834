<?php
class Agent {
    private $conn;
    private $table_name = "agents";

    public $id;
    public $name;
    public $email;
    public $password;
    public $subscription_type;
    public $property_count;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllAgents($page = 1, $limit = 10, $status = null) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM " . $this->table_name;
        if ($status) {
            $query .= " WHERE status = :status";
        }
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        if ($status) {
            $stmt->bindParam(":status", $status);
        }
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getTotalAgentCount($status = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        if ($status) {
            $query .= " WHERE status = :status";
        }

        $stmt = $this->conn->prepare($query);
        if ($status) {
            $stmt->bindParam(":status", $status);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    public function approve() {
        $query = "UPDATE " . $this->table_name . " 
                SET status = 'approved' 
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function search($keywords) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE name LIKE :keywords 
                OR email LIKE :keywords 
                ORDER BY created_at DESC";

        $keywords = "%{$keywords}%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":keywords", $keywords);
        $stmt->execute();

        return $stmt;
    }

    public function canAddProperty() {
        $property_limits = [
            'base' => 4,
            'gold' => 10,
            'vip' => PHP_INT_MAX
        ];

        $limit = $property_limits[$this->subscription_type] ?? 0;
        return $this->property_count < $limit;
    }

    public function incrementPropertyCount() {
        $query = "UPDATE " . $this->table_name . "
                SET property_count = property_count + 1
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function decrementPropertyCount() {
        $query = "UPDATE " . $this->table_name . "
                SET property_count = GREATEST(0, property_count - 1)
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }
}
?>
