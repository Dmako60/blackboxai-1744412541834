<?php
class Reservation {
    private $conn;
    private $table_name = "reservations";

    public $id;
    public $property_id;
    public $user_id;
    public $check_in;
    public $check_out;
    public $guests_count;
    public $total_price;
    public $status;
    public $cancellation_reason;
    public $special_requests;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (property_id, user_id, check_in, check_out, guests_count, 
                total_price, status, special_requests)
                VALUES
                (:property_id, :user_id, :check_in, :check_out, :guests_count,
                :total_price, :status, :special_requests)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->property_id = htmlspecialchars(strip_tags($this->property_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->check_in = htmlspecialchars(strip_tags($this->check_in));
        $this->check_out = htmlspecialchars(strip_tags($this->check_out));
        $this->guests_count = htmlspecialchars(strip_tags($this->guests_count));
        $this->total_price = htmlspecialchars(strip_tags($this->total_price));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->special_requests = htmlspecialchars(strip_tags($this->special_requests));

        // Bind values
        $stmt->bindParam(":property_id", $this->property_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":check_in", $this->check_in);
        $stmt->bindParam(":check_out", $this->check_out);
        $stmt->bindParam(":guests_count", $this->guests_count);
        $stmt->bindParam(":total_price", $this->total_price);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":special_requests", $this->special_requests);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function getById($id = null) {
        $query = "SELECT r.*, p.title as property_title, p.location as property_location,
                    u.name as user_name, u.email as user_email
                FROM " . $this->table_name . " r
                LEFT JOIN properties p ON r.property_id = p.id
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id ?? $this->id);
        $stmt->execute();

        return $stmt;
    }

    public function getByUser($user_id, $page = 1, $limit = 10, $status = null) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT r.*, p.title as property_title, p.location as property_location
                FROM " . $this->table_name . " r
                LEFT JOIN properties p ON r.property_id = p.id
                WHERE r.user_id = :user_id";

        if ($status) {
            $query .= " AND r.status = :status";
        }

        $query .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        if ($status) {
            $stmt->bindParam(":status", $status);
        }
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function isAvailable($property_id, $check_in, $check_out, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count
                FROM " . $this->table_name . "
                WHERE property_id = :property_id
                AND status != 'cancelled'
                AND ((check_in BETWEEN :check_in AND :check_out)
                OR (check_out BETWEEN :check_in AND :check_out)
                OR (check_in <= :check_in AND check_out >= :check_out))";

        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":property_id", $property_id);
        $stmt->bindParam(":check_in", $check_in);
        $stmt->bindParam(":check_out", $check_out);
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] == 0;
    }

    public function cancel() {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status,
                    cancellation_reason = :cancellation_reason,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":cancellation_reason", $this->cancellation_reason);

        return $stmt->execute();
    }

    public function updateDates() {
        $query = "UPDATE " . $this->table_name . "
                SET check_in = :check_in,
                    check_out = :check_out,
                    total_price = :total_price,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":check_in", $this->check_in);
        $stmt->bindParam(":check_out", $this->check_out);
        $stmt->bindParam(":total_price", $this->total_price);

        return $stmt->execute();
    }

    public function updateGuestsCount() {
        $query = "UPDATE " . $this->table_name . "
                SET guests_count = :guests_count,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":guests_count", $this->guests_count);

        return $stmt->execute();
    }

    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":status", $this->status);

        return $stmt->execute();
    }

    public function getTotalCount($user_id, $status = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . "
                WHERE user_id = :user_id";

        if ($status) {
            $query .= " AND status = :status";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        if ($status) {
            $stmt->bindParam(":status", $status);
        }
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getUpcoming($user_id, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT r.*, p.title as property_title, p.location as property_location
                FROM " . $this->table_name . " r
                LEFT JOIN properties p ON r.property_id = p.id
                WHERE r.user_id = :user_id
                AND r.status = 'confirmed'
                AND r.check_in >= CURRENT_DATE
                ORDER BY r.check_in ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getPast($user_id, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT r.*, p.title as property_title, p.location as property_location
                FROM " . $this->table_name . " r
                LEFT JOIN properties p ON r.property_id = p.id
                WHERE r.user_id = :user_id
                AND r.check_out < CURRENT_DATE
                ORDER BY r.check_out DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }
}
?>
