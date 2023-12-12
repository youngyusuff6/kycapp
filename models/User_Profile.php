<?php
class User_Profile {
    private $conn;
    private $table = 'user_profiles';

    public $id;
    public $user_id;
    public $mobile_phone;
    public $date_of_birth;
    public $address;
    public $cv_path;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create user profile (signup)
    public function create() {
        $query = 'INSERT INTO ' . $this->table . '
                  SET user_id = :user_id,
                      mobile_phone = :mobile_phone,
                      date_of_birth = :date_of_birth,
                      address = :address,
                      cv_path = :cv_path';

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->mobile_phone = htmlspecialchars(strip_tags($this->mobile_phone));
        $this->date_of_birth = htmlspecialchars(strip_tags($this->date_of_birth));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->cv_path = htmlspecialchars(strip_tags($this->cv_path));

        // Bind data
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':mobile_phone', $this->mobile_phone);
        $stmt->bindParam(':date_of_birth', $this->date_of_birth);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':cv_path', $this->cv_path);

        if ($stmt->execute()) {
            return true;
        }

        printf('Error: %s.\n', $stmt->error);

        return false;
    }

    // Read user profile by user ID (for editing profile)
    public function readByUserId() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE user_id = ? LIMIT 0,1';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->user_id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->id = $row['id'];
        $this->mobile_phone = $row['mobile_phone'];
        $this->date_of_birth = $row['date_of_birth'];
        $this->address = $row['address'];
        $this->cv_path = $row['cv_path'];
    }
        // Delete user profile by ID
        public function deleteUserProfileById($user_id) {
            $query = 'DELETE FROM ' . $this->table . ' WHERE user_id = ?';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id);
            return $stmt->execute();
        }

    // Update user profile (edit profile)
    public function update() {
        $query = 'UPDATE ' . $this->table . '
                  SET mobile_phone = :mobile_phone,
                      date_of_birth = :date_of_birth,
                      address = :address,
                      cv_path = :cv_path
                  WHERE user_id = :user_id';
            $stmt = $this->conn->prepare($query);
    
        // Clean data
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->mobile_phone = htmlspecialchars(strip_tags($this->mobile_phone));
        $this->date_of_birth = htmlspecialchars(strip_tags($this->date_of_birth));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->cv_path = htmlspecialchars(strip_tags($this->cv_path));
    
        // Bind data
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':mobile_phone', $this->mobile_phone);
        $stmt->bindParam(':date_of_birth', $this->date_of_birth);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':cv_path', $this->cv_path);
    
        if ($stmt->execute()) {
            return true;
        }
    
        printf('Error: %s.\n', $stmt->error);
    
        return false;
    }
    
    
    
    
    //For API use
    public function updateProfileRecord($user_id, $mobile_phone, $date_of_birth, $address, $cv_path) {
        $query = "UPDATE User_Profiles SET mobile_phone = :mobile_phone, date_of_birth = :date_of_birth, address = :address, cv_path = :cv_path WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindParam(':mobile_phone', $mobile_phone);
        $stmt->bindParam(':date_of_birth', $date_of_birth);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':cv_path', $cv_path);
        $stmt->bindParam(':user_id', $user_id);
    
        $stmt->execute();
    
        return $stmt;
    }
    public function getUserProfileByUserId($user_id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE user_id = ? LIMIT 0,1';
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
    
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return [
            'id' => $row['id'],
            'mobile_phone' => $row['mobile_phone'],
            'date_of_birth' => $row['date_of_birth'],
            'address' => $row['address'],
            'cv_path' => $row['cv_path']
        ];
    }
    
    
}
