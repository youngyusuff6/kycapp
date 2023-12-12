<?php
class EditProfileController {
    private $user;
    private $user_profile;

    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
        $this->user_profile = new User_Profile($db);
    }

      // Load user profile and user data for editing
      public function loadProfile($user_id) {
        // Fetch user data
        $this->user->id = $user_id;
        $this->user->readById();  // Assuming you have a readById method in your User class

        // Fetch user profile data
        $this->user_profile->user_id = $user_id;
        $this->user_profile->readByUserId();

        return [
            'user' => $this->user,
            'user_profile' => $this->user_profile
        ];
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
    
// Save edited user profile
public function saveProfile($user, $fileInfo) {
    // Assuming $user is an instance of the User model
    $user->id = $_POST['user_id'];
    $user->name = $_POST['name'];
    // Save the updated user data
    $userResult = $this->user->update($user->id, $user->name);

    // Assuming $this->user_profile is an instance of the User_Profile model
    $this->user_profile->user_id = $_POST['user_id'];
    $this->user_profile->mobile_phone = $_POST['mobile_phone'];
    $this->user_profile->date_of_birth = $_POST['date_of_birth'];
    $this->user_profile->address = $_POST['address'];

    // Check if a new file has been uploaded
    if ($fileInfo !== null && $fileInfo['error'] == UPLOAD_ERR_OK) {
        // Assuming you have a method to handle file uploads in your EditProfileController
        $this->user_profile->cv_path = $this->handleFileUpload($fileInfo);
    } 
    else {
        // If no new file is uploaded, retain the existing CV path if it's not empty
        $this->user_profile->cv_path = !empty($_POST['existing_cv_path']) ? $_POST['existing_cv_path'] : null;
    }

    // Save the updated user profile data
    $profileResult = $this->user_profile->update();

    // Return true if both updates were successful, otherwise return false
    return $userResult && $profileResult;
}



public function getRecordOwner($user_id) {
    // Fetch user data
    $this->user->id = $user_id;
    $this->user->readById();

    // Assuming your User class has a readById method

    // Check if the user data is fetched successfully
    if ($this->user->id) {
        return $this->user->id; // Return user ID as the record owner
    } else {
        return null; // Return null if user data is not found
    }
}

//API USE
public function getMergedUserData($user_id) {
    // Fetch user data
    $userData = $this->user->getUserById($user_id);

    // Fetch user profile data
    $userProfileData = $this->user_profile->getUserProfileByUserId($user_id);

    // Merge user and user profile data
    $mergedData = array_merge($userData, $userProfileData);

    return $mergedData;
}



public function updateRecord($user_id, $name, $mobile_phone, $date_of_birth, $address, $cv_path) {
    $this->user->updateUserName($user_id, $name);
    return $this->user_profile->updateProfileRecord($user_id, $mobile_phone, $date_of_birth, $address, $cv_path);
}


}
