<?php
class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $name;
    public $email;
    public $password;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create user (signup)
    public function create() {
        $query = 'INSERT INTO ' . $this->table . '
                  SET name = :name,
                      email = :email,
                      password = :password';

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));

        // Bind data
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);

        if ($stmt->execute()) {
            // Get the user ID of the newly created user
            $this->id = $this->conn->lastInsertId();

            return true;
        }

        printf('Error: %s.\n', $stmt->error);

        return false;
    }

    // Read user by email (for login)
    public function readByEmail() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE email = ? LIMIT 0,1';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->email);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->email = $row['email'];
        $this->password = $row['password'];
        $this->created_at = $row['created_at'];
    }

   // Read user by id (for record fetching)
    public function readById() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = ? LIMIT 0,1';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->email = $row['email'];
        $this->password = $row['password'];
        $this->created_at = $row['created_at'];
    }

    // Update user (edit profile)
  
    public function update($id, $name) {
        $query = 'UPDATE ' . $this->table . ' SET name = :name WHERE id = :id';
    
        $stmt = $this->conn->prepare($query);
    
        // Clean data
        $name = htmlspecialchars(strip_tags($name));
        $id = htmlspecialchars(strip_tags($id));
     
    
        // Bind data
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':id', $id);
    
        if ($stmt->execute()) {
            return true;
        }
    
        printf('Error: %s.\n', $stmt->error);
    
        return false;
    }
    
    public function deleteUserById($user_id) {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        return $stmt->execute();
    }

    //Fpr API use
  public function updateUserName($user_id, $name) {
    $query = "UPDATE users SET name = :name WHERE id = :user_id";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':user_id', $user_id);

    $stmt->execute();

    return $stmt;
}

public function getUserById($id) {
    $query = 'SELECT * FROM ' . $this->table . ' WHERE id = ? LIMIT 0,1';

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'id' => $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'password' => $row['password'],
        'created_at' => $row['created_at']
    ];
}


    
    

    

    
}
