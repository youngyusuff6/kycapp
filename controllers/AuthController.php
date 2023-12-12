<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/User_Profile.php'; 
require_once __DIR__ . '/../core/Session.php';  

class AuthController {
    private $user;
    private $user_profile;
    private $conn;

    public function __construct($db) {
        $this->user = new User($db);
        $this->user_profile = new User_Profile($db);
        $this->conn = $db;
    }

    public function getDB() {
        return $this->conn;
    }

    // Signup

    public function signup($name, $email, $hashedPassword, $mobile_phone, $date_of_birth, $address, $cv_path) {
        // Check if the email is already registered
        if ($this->isEmailRegistered($email)) {
            return false; // Email is already registered
        }
    
        // Create a new user
        $this->user->name = $name;
        $this->user->email = $email;
        $this->user->password = $hashedPassword;
    
        if ($this->user->create()) {
            // Get the user ID of the newly created user
            $user_id = $this->user->id;
    
            // Create user profile
            $this->user_profile->user_id = $user_id;
            $this->user_profile->mobile_phone = $mobile_phone;
            $this->user_profile->date_of_birth = $date_of_birth;
            $this->user_profile->address = $address;
            $this->user_profile->cv_path = $cv_path;
    
            if ($this->user_profile->create()) {
                return true; // Signup successful
            } else {
                // Handle user profile creation error
                // Rollback user creation if needed
                $this->user->deleteUserById($user_id); // Rollback user creation by deleting the user
                return false;
            }
        } else {
            // Handle user creation error
            return false;
        }
    }
    public function isEmailRegistered($email) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $count = $stmt->fetchColumn();
            return ($count > 0);
        } catch (PDOException $e) {
            // Handle the exception (you might want to log or print the error message)
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    
    public function login($email, $password) {
        try {
            // Read user by email
            $this->user->email = $email;
            $this->user->readByEmail();
    
            // Check if user exists and password is correct
            if ($this->user->id && password_verify($password, $this->user->password)) {
                // Set user session
                Session::set('user_id', $this->user->id);
                Session::set('email', $this->user->email);
            // Return the user object
            return $this->user;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            // Handle the exception (you might want to log or print the error message)
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    public function logout() {
        Session::start();
        Session::destroy();
    }

    public function deleteUser($user_id) {
        return $this->user->deleteUserById($user_id);
    }

    public function deleteUserProfile($user_id) {
        return $this->user_profile->deleteUserProfileById($user_id);
    }
    
    public function handleFileUpload($file) {
        $uploadDirectory = '../public/uploads/';
    
        // Create the target directory if it doesn't exist
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }
    
        // Generate a unique filename to avoid overwriting existing files
        $uniqueFilename = "UserCV_" . uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $targetPath = $uploadDirectory . $uniqueFilename;
    
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $targetPath; 
        } else {
            return false;
        }
    }
    
    
}
