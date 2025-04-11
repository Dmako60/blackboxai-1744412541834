<?php
class Property {
    private $conn;
    private $table_name = "properties";
    private $images_table = "property_images";

    public $id;
    public $agent_id;
    public $title;
    public $description;
    public $price_per_night;
    public $location;
    public $latitude;
    public $longitude;
    public $amenities;
    public $rules;
    public $max_guests;
    public $bedrooms;
    public $bathrooms;
    public $status;
    public $youtube_url;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllProperties($page = 1, $limit = 10, $status = null, $agent_id = null) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT p.*, 
                    GROUP_CONCAT(DISTINCT pi.image_url) as image_urls,
                    a.name as agent_name,
                    a.email as agent_email
                FROM " . $this->table_name . " p
                LEFT JOIN " . $this->images_table . " pi ON p.id = pi.property_id
                LEFT JOIN agents a ON p.agent_id = a.id";

        $conditions = [];
        if ($status) {
            $conditions[] = "p.status = :status";
        }
        if ($agent_id) {
            $conditions[] = "p.agent_id = :agent_id";
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if ($status) {
            $stmt->bindParam(":status", $status);
        }
        if ($agent_id) {
            $stmt->bindParam(":agent_id", $agent_id, PDO::PARAM_INT);
        }
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    public function getTotalPropertyCount($status = null, $agent_id = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        $conditions = [];
        if ($status) {
            $conditions[] = "status = :status";
        }
        if ($agent_id) {
            $conditions[] = "agent_id = :agent_id";
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->conn->prepare($query);
        
        if ($status) {
            $stmt->bindParam(":status", $status);
        }
        if ($agent_id) {
            $stmt->bindParam(":agent_id", $agent_id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (agent_id, title, description, price_per_night, location, 
                latitude, longitude, amenities, rules, max_guests, 
                bedrooms, bathrooms, status, youtube_url)
                VALUES
                (:agent_id, :title, :description, :price_per_night, :location,
                :latitude, :longitude, :amenities, :rules, :max_guests,
                :bedrooms, :bathrooms, :status, :youtube_url)";

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->agent_id = htmlspecialchars(strip_tags($this->agent_id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->youtube_url = htmlspecialchars(strip_tags($this->youtube_url));

        $stmt->bindParam(":agent_id", $this->agent_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price_per_night", $this->price_per_night);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":amenities", $this->amenities);
        $stmt->bindParam(":rules", $this->rules);
        $stmt->bindParam(":max_guests", $this->max_guests);
        $stmt->bindParam(":bedrooms", $this->bedrooms);
        $stmt->bindParam(":bathrooms", $this->bathrooms);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":youtube_url", $this->youtube_url);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET title = :title,
                    description = :description,
                    price_per_night = :price_per_night,
                    location = :location,
                    latitude = :latitude,
                    longitude = :longitude,
                    amenities = :amenities,
                    rules = :rules,
                    max_guests = :max_guests,
                    bedrooms = :bedrooms,
                    bathrooms = :bathrooms,
                    status = :status,
                    youtube_url = :youtube_url
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->youtube_url = htmlspecialchars(strip_tags($this->youtube_url));

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price_per_night", $this->price_per_night);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":amenities", $this->amenities);
        $stmt->bindParam(":rules", $this->rules);
        $stmt->bindParam(":max_guests", $this->max_guests);
        $stmt->bindParam(":bedrooms", $this->bedrooms);
        $stmt->bindParam(":bathrooms", $this->bathrooms);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":youtube_url", $this->youtube_url);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function updateStatus($status) {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":status", $status);

        return $stmt->execute();
    }

    public function search($keywords) {
        $query = "SELECT p.*, 
                    GROUP_CONCAT(DISTINCT pi.image_url) as image_urls,
                    a.name as agent_name,
                    a.email as agent_email
                FROM " . $this->table_name . " p
                LEFT JOIN " . $this->images_table . " pi ON p.id = pi.property_id
                LEFT JOIN agents a ON p.agent_id = a.id
                WHERE p.title LIKE :keywords 
                OR p.description LIKE :keywords 
                OR p.location LIKE :keywords
                GROUP BY p.id
                ORDER BY p.created_at DESC";

        $keywords = "%{$keywords}%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":keywords", $keywords);
        $stmt->execute();

        return $stmt;
    }

    public function addImage($image_url, $is_primary = false) {
        $query = "INSERT INTO " . $this->images_table . "
                (property_id, image_url, is_primary)
                VALUES (:property_id, :image_url, :is_primary)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":property_id", $this->id);
        $stmt->bindParam(":image_url", $image_url);
        $stmt->bindParam(":is_primary", $is_primary, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    public function removeImage($image_id) {
        $query = "DELETE FROM " . $this->images_table . "
                WHERE id = :image_id AND property_id = :property_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":image_id", $image_id);
        $stmt->bindParam(":property_id", $this->id);

        return $stmt->execute();
    }

    public function getById($id = null) {
        $query = "SELECT p.*, 
                    GROUP_CONCAT(DISTINCT pi.image_url) as image_urls,
                    a.name as agent_name,
                    a.email as agent_email
                FROM " . $this->table_name . " p
                LEFT JOIN " . $this->images_table . " pi ON p.id = pi.property_id
                LEFT JOIN agents a ON p.agent_id = a.id
                WHERE p.id = :id
                GROUP BY p.id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id ?? $this->id);
        $stmt->execute();

        return $stmt;
    }
}
?>
