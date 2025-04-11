<?php
class Payment {
    private $conn;
    private $table_name = "payments";

    public $id;
    public $reservation_id;
    public $user_id;
    public $amount;
    public $payment_method;
    public $status;
    public $transaction_id;
    public $payment_details;
    public $payment_type;
    public $original_payment_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (reservation_id, amount, payment_method, status, 
                transaction_id, payment_details)
                VALUES
                (:reservation_id, :amount, :payment_method, :status,
                :transaction_id, :payment_details)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->reservation_id = htmlspecialchars(strip_tags($this->reservation_id));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->transaction_id = htmlspecialchars(strip_tags($this->transaction_id));

        // Bind values
        $stmt->bindParam(":reservation_id", $this->reservation_id);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":payment_method", $this->payment_method);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":transaction_id", $this->transaction_id);
        $stmt->bindParam(":payment_details", $this->payment_details);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function createSubscriptionPayment() {
        $query = "INSERT INTO " . $this->table_name . "
                (user_id, amount, payment_method, status, 
                transaction_id, payment_type, payment_details)
                VALUES
                (:user_id, :amount, :payment_method, :status,
                :transaction_id, :payment_type, :payment_details)";

        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":payment_method", $this->payment_method);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":transaction_id", $this->transaction_id);
        $stmt->bindParam(":payment_type", $this->payment_type);
        $stmt->bindParam(":payment_details", $this->payment_details);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function createRefund() {
        $query = "INSERT INTO " . $this->table_name . "
                (original_payment_id, amount, payment_method, status,
                transaction_id, payment_type, payment_details)
                VALUES
                (:original_payment_id, :amount, :payment_method, :status,
                :transaction_id, :payment_type, :payment_details)";

        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":original_payment_id", $this->original_payment_id);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":payment_method", $this->payment_method);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":transaction_id", $this->transaction_id);
        $stmt->bindParam(":payment_type", $this->payment_type);
        $stmt->bindParam(":payment_details", $this->payment_details);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function getById($id = null) {
        $query = "SELECT p.*, r.user_id, r.property_id, r.check_in, r.check_out
                FROM " . $this->table_name . " p
                LEFT JOIN reservations r ON p.reservation_id = r.id
                WHERE p.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id ?? $this->id);
        $stmt->execute();

        return $stmt;
    }

    public function getByReservation($reservation_id) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE reservation_id = :reservation_id
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":reservation_id", $reservation_id);
        $stmt->execute();

        return $stmt;
    }

    public function getByUser($user_id, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;

        $query = "SELECT p.*, r.check_in, r.check_out, pr.title as property_title
                FROM " . $this->table_name . " p
                LEFT JOIN reservations r ON p.reservation_id = r.id
                LEFT JOIN properties pr ON r.property_id = pr.id
                WHERE r.user_id = :user_id
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":status", $status);

        return $stmt->execute();
    }

    public function getTotalByUser($user_id) {
        $query = "SELECT COUNT(*) as total
                FROM " . $this->table_name . " p
                LEFT JOIN reservations r ON p.reservation_id = r.id
                WHERE r.user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getSubscriptionPayments($user_id) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE user_id = :user_id
                AND payment_type = 'subscription'
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt;
    }

    public function getLatestSubscription($user_id) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE user_id = :user_id
                AND payment_type = 'subscription'
                AND status = 'completed'
                ORDER BY created_at DESC
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt;
    }

    public function getRefunds($payment_id) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE original_payment_id = :payment_id
                AND payment_type = 'refund'
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":payment_id", $payment_id);
        $stmt->execute();

        return $stmt;
    }

    public function getTotalRefunds($payment_id) {
        $query = "SELECT COALESCE(SUM(amount), 0) as total
                FROM " . $this->table_name . "
                WHERE original_payment_id = :payment_id
                AND payment_type = 'refund'
                AND status = 'completed'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":payment_id", $payment_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
