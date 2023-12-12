<?php
require_once 'config.php';
require_once 'core/Database.php';
require_once 'models/User.php';
require_once 'models/User_Profile.php';
require_once 'controllers/AuthController.php';
require_once __DIR__ . '/controllers/EditProfileController.php';
require_once __DIR__ . '/vendor/autoload.php'; 

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Initialize the database connection
$db = (new Database())->connect();

// Initialize the AuthController and RecordController
$authController = new AuthController($db);
$editProfileController = new EditProfileController($db);

// Check the action parameter
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Perform actions based on the HTTP method and action parameter
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        // Handle POST requests
        if ($action === 'login') {
            handleLogin($authController);
        }elseif ($action === 'logout') {
            handleLogout($authController);
        }
        break;
    case 'GET':
        if ($action === 'get_profile') {
            $user_id = isset($_GET['id']) ? $_GET['id'] : null;
            handleGetUser($editProfileController, $user_id);
        }
        break;
    case 'PUT':
        // Handle PUT requests (for editing records)
        if ($action === 'edit_profile') {
            $user_id = isset($_GET['id']) ? $_GET['id'] : null;
            handleEditUser($editProfileController, $user_id);
        }
        break;
    case 'DELETE':
        // Handle DELETE requests (for deleting records)
        if ($action === 'delete_profile') {
            $user_id = isset($_GET['id']) ? $_GET['id'] : null;
            handleDeleteUser($authController, $editProfileController ,$user_id);
        }
        break;
    default:
        // Handle other cases or send an error response
        http_response_code(400);
        echo json_encode(array('message' => 'Invalid request method'));
        break;
}



// Function to handle login and set the session
function handleLogin($authController) {
    // Get the input data (email and password)
    $data = json_decode(file_get_contents("php://input"));

    // Validate input
    if (empty($data->email) || empty($data->password)) {
        http_response_code(400);
        echo json_encode(array('message' => 'Invalid input: Email and password are required'));
        return;
    }

    // Call the login method
    $loginResult = $authController->login($data->email, $data->password);

    if ($loginResult) {
        // Generate JWT token
       $tokenInfo = generateToken($loginResult);

        http_response_code(200);
        echo json_encode(array('message' => 'Login successful', 'token' => $tokenInfo));
    } else {
        http_response_code(401);
        echo json_encode(array('message' => 'Login failed: Invalid credentials'));
    }
}
//Function to get logged in users profile details
function handleGetUser($editProfileController, $user_id){
    $headers = getallheaders();
    $jwtToken = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    // Validate the JWT token
    if (!isValidToken($jwtToken)) {
        http_response_code(401);
        echo json_encode(array('message' => 'Token not valid'));
        return;
    }

    // Check if the record belongs to the logged-in user
    $userIdFromToken = isValidToken($jwtToken);
    $recordOwner = $editProfileController->getRecordOwner($user_id);

    if ($userIdFromToken !== $recordOwner) {
        http_response_code(403);
        echo json_encode(array('message' => 'Forbidden: You do not have permission to view this profile'));
        return;
    }

    // Load user profile and user data for editing
    $userData = $editProfileController->loadProfile($user_id);

    // Return the user profile and user data
    http_response_code(200);
    echo json_encode($userData);
}

function handleEditUser($editProfileController, $user_id) {
    // Get the JWT token from the headers
    $headers = getallheaders();
    $jwtToken = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    // Validate the JWT token
    if (!isValidToken($jwtToken)) {
        http_response_code(401);
        echo json_encode(array('message' => 'Token not valid'));
        return;
    }

    // Check if the record belongs to the logged-in user
    $userIdFromToken = isValidToken($jwtToken);
    $recordOwner = $editProfileController->getRecordOwner($user_id);

    if ($userIdFromToken !== $recordOwner) {
        http_response_code(403);
        echo json_encode(array('message' => 'Forbidden: You do not have permission to edit this record'));
        return;
    }

    // Get existing merged record data
    $existingMergedRecord = $editProfileController->getMergedUserData($user_id);

    // Check if content type is JSON or form data
    $isJson = $_SERVER['CONTENT_TYPE'] === 'application/json';

    // Initialize variables for form data
    $name = $mobile_phone = $email = $date_of_birth = $address = $cv_path = null;

    // Get the update data from the request body
    if ($isJson) {
        $formData = json_decode(file_get_contents("php://input"));
        // Extract fields from JSON data
        $name = $formData->name ?? $existingMergedRecord['name'];
        $mobile_phone = $formData->mobile_phone ?? $existingMergedRecord['mobile_phone'];
        $date_of_birth = $formData->date_of_birth ?? $existingMergedRecord['date_of_birth'];
        $address = $formData->address ?? $existingMergedRecord['address'];
    } else {
        // For form data, you can access $_POST directly
        $name = $_POST['name'] ?? $existingMergedRecord['name'];
        $mobile_phone = $_POST['mobile_phone'] ?? $existingMergedRecord['mobile_phone'];
        $email = $_POST['email'] ?? $existingMergedRecord['email'];
        $date_of_birth = $_POST['date_of_birth'] ?? $existingMergedRecord['date_of_birth'];
        $address = $_POST['address'] ?? $existingMergedRecord['address'];

        // Check if file is uploaded and handle it
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] == UPLOAD_ERR_OK) {
            $cv_path = $editProfileController->handleFileUpload($_FILES['cv']);
        }
    }

    // Call the method to update the record
    $result = $editProfileController->updateRecord(
        $user_id,
        $name,
        $mobile_phone,
        $date_of_birth,
        $address,
        $cv_path ?? $existingMergedRecord['cv_path']
    );

    // Check the result and send the appropriate response
    if ($result) {
        http_response_code(200);
        echo json_encode(array('message' => 'Record updated successfully'));
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Failed to update record'));
    }
}



// Function to handle the deletion of a record
function handleDeleteUser($authController, $editProfileController, $user_id) {
    // Get the JWT token from the headers
    $headers = getallheaders();
    $jwtToken = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    // Validate the JWT token
    if (!isValidToken($jwtToken)) {
        http_response_code(401);
        echo json_encode(array('message' => 'Token not valid'));
        return;
    }

    // Check if the record belongs to the logged-in user
    $userIdFromToken = isValidToken($jwtToken);
    $recordOwner = $editProfileController->getRecordOwner($user_id);

    if ($userIdFromToken !== $recordOwner) {
        http_response_code(403);
        echo json_encode(array('message' => 'Forbidden: You do not have permission to delete this record'));
        return;
    }

    // Call the method to delete the record
    $result = $authController->deleteUserProfile($user_id) && $authController->deleteUser($user_id);

    if ($result) {
        // Logout the user after deletion
        $authController->logout();

        http_response_code(200);
        echo json_encode(array('message' => 'User deleted successfully and logged out'));
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Failed to delete User'));
    }
}

// Add this function to handle user logout
function handleLogout($authController) {
    // Get the JWT token from the headers
    $headers = getallheaders();
    $jwtToken = isset($headers['Authorization']) ? $headers['Authorization'] : '';

    // Validate the JWT token
    if (!isValidToken($jwtToken)) {
        http_response_code(401);
        echo json_encode(array('message' => 'Token not valid'));
        return;
    }

    // Call the logout method
    $logoutResult = $authController->logout();

    if ($logoutResult) {
        http_response_code(200);
        echo json_encode(array('message' => 'Logout successful'));
    } else {
        http_response_code(500);
        echo json_encode(array('message' => 'Failed to logout'));
    }
}

// Function to handle file upload
function handleFileUpload($file) {
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


// Function to detokenize the JWT token and get user information
function isValidToken($jwtToken) {
    // Your secret key for signing the token
    $secretKey = 'bGS6lzFqvvSQ8ALbOxatm7/Vk7mLQyzqaS34Q4oR1ew='; 

     // Extract the token from the "Bearer" prefix
     $token = str_replace('Bearer ', '', $jwtToken);
     try {
         // Decode the token
         $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
 
         // Assuming your JWT includes a 'user_id' claim
         $userId = $decoded->user_id;

         // You can store the user information in a global variable or return it
         // for further processing in your application
         return $userId;
    } catch (Exception $e) {
        return false;
    }
}

// Function to generate a JWT token
function generateToken($userData) {
    $secretKey = 'bGS6lzFqvvSQ8ALbOxatm7/Vk7mLQyzqaS34Q4oR1ew='; 

   // $userData is an object
    $userId = $userData->id ?? null;
    $email = $userData->email ?? null;


    
    $tokenData = [
        'user_id' => $userId,
        'email'   => $email,
    ];

    $token = JWT::encode($tokenData, $secretKey, 'HS256');

    return [
        'token' => $token
    ];
}



?>
