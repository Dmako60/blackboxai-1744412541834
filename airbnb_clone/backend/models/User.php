<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $name;
    public $email;
    public $password;
    public $phone;
    public $profile_image;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (name, email, password, phone, profile_image)
                VALUES
                (:name, :email, :password, :phone, :profile_image)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->profile_image = htmlspecialchars(strip_tags($this->profile_image));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":profile_image", $this->profile_image);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET name = :name,
                    phone = :phone,
                    profile_image = :profile_image,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->profile_image = htmlspecialchars(strip_tags($this->profile_image));

        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":profile_image", $this->profile_image);

        return $stmt->execute();
    }

    public function updatePassword() {
        $query = "UPDATE " . $this->table_name . "
                SET password = :password,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Bind values
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":password", $this->password);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    public function getById($id = null) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id ?? $this->id);
        $stmt->execute();

        return $stmt;
    }

    public function getByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        return $stmt;
    }

    public function emailExists($email) {
        $stmt = $this->getByEmail($email);
        return $stmt->rowCount() > 0;
    }

    public function getReservations($page = 1, $limit = 10, $status = null) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT r.*, p.title as property_title, p.location as property_location,
                    a.name as agent_name
                FROM reservations r
                LEFT JOIN properties p ON r.property_id = p.id
                LEFT JOIN agents a ON p.agent_id = a.id
                WHERE r.user_id = :user_id";

        if ($status) {
            $query .= " AND r.status = :status";
        }

        $query .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        if ($status) {
            $stmt->bindParam(":status", $status);
        }
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getTotalReservations($status = null) {
        $query = "SELECT COUNT(*) as total FROM reservations WHERE user_id = :user_id";
        
        if ($status) {
            $query .= " AND status = :status";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        if ($status) {
            $stmt->bindParam(":status", $status);
        }
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getFavoriteProperties($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT p.*, a.name as agent_name,
                    GROUP_CONCAT(pi.image_url) as image_urls
                FROM user_favorites uf
                LEFT JOIN properties p ON uf.property_id = p.id
                LEFT JOIN agents a ON p.agent_id = a.id
                LEFT JOIN property_images pi ON p.id = pi.property_id
                WHERE uf.user_id = :user_id
                GROUP BY p.id
                ORDER BY uf.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getTotalFavorites() {
        $query = "SELECT COUNT(*) as total FROM user_favorites WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function addToFavorites($property_id) {
        $query = "INSERT INTO user_favorites (user_id, property_id)
                VALUES (:user_id, :property_id)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->bindParam(":property_id", $property_id);

        return $stmt->execute();
    }

    public function removeFromFavorites($property_id) {
        $query = "DELETE FROM user_favorites 
                WHERE user_id = :user_id AND property_id = :property_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->bindParam(":property_id", $property_id);

        return $stmt->execute();
    }

    public function isFavorite($property_id) {
        $query = "SELECT COUNT(*) as count FROM user_favorites 
                WHERE user_id = :user_id AND property_id = :property_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->bindParam(":property_id", $property_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }
}
?>
