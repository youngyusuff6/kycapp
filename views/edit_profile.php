<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once '../core/Session.php';
require_once '../core/Database.php';
require_once '../models/User.php';
require_once '../models/User_Profile.php';
require_once '../controllers/AuthController.php';
require_once '../controllers/EditProfileController.php';
require_once '../common.php';

function assets($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Check for an active user session
$session = new Session();
$user_id = $session->get('user_id');

// Create instances of necessary classes
$db = new Database();
$conn = $db->connect();
$user = new User($conn);

// Fetch user data
$user->id = $user_id;
$user->readById();

// Fetch user profile for display
$user_profile = new User_Profile($conn);
$user_profile->user_id = $user_id;
$user_profile->readByUserId();

// Initialize auth controller
$authController = new AuthController($conn);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_profile') {
    handleEditProfile($authController, $user_profile);
}

// Function to handle edit profile
// Function to handle edit profile
function handleEditProfile($authController, $user_profile) {
    global $errors, $successMessage; // Access global variables

    $conn = $authController->getDB();
    $editProfileController = new EditProfileController($conn);

    // Retrieve the form data
    $user_profile->user_id = $_POST['user_id'];
    $user_profile->name = $_POST['name'];
    $user_profile->mobile_phone = $_POST['mobile_phone'];
    $user_profile->date_of_birth = $_POST['date_of_birth'];
    $user_profile->address = $_POST['address'];

    // Check if a new file has been uploaded
    $fileInfo = isset($_FILES['cv_path']) ? $_FILES['cv_path'] : null;

    // Validate the form data (you may need more thorough validation)
    if (empty($user_profile->name) || empty($user_profile->date_of_birth) || empty($user_profile->address) || empty($user_profile->mobile_phone)) {
         $_SESSION['error_message'] = "All fields are required.";
    } else {
        // Save the edited user profile
        $result = $editProfileController->saveProfile($user_profile, $fileInfo);
        // Return to the page after editing with message
        if ($result) {        
            $_SESSION['success_message'] = "Record updated successfully!";
            header('Location: ' . BASE_URL . '/views/profile.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Failed to update the record. Please try again.";
        }
    }
}

?>
<!-- Display edit profile form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <!-- Bootstrap CSS link -->
    <link href="<?php echo assets('public/assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
</head>
<body>
      <!-- Include the Bootstrap Navbar code here -->
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Your App Name</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="edit_profile.php">Edit Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?action=logout">Logout</a>
                    </li>
                </ul>

            </div>
        </div>
    </nav>
    <!-- Include the Bootstrap Navbar code here -->

    <div class="container mt-4">
        <h2>Edit Profile</h2>
        <?php
            // Display error message if any
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']); // Clear the message after displaying it
            }
        ?>
        <!-- Display edit profile form -->
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit_profile">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

              <!-- Name -->
              <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user->name); ?>" required>
            </div>

            <!-- Mobile Phone -->
            <div class="mb-3">
                <label for="mobile_phone" class="form-label">Mobile Phone</label>
                <input type="text" class="form-control" id="mobile_phone" name="mobile_phone" value="<?php echo htmlspecialchars($user_profile->mobile_phone); ?>" required>
            </div>

            <!-- Date of Birth -->
            <div class="mb-3">
                <label for="date_of_birth" class="form-label">Date of Birth</label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($user_profile->date_of_birth); ?>" required>
            </div>

            <!-- Address -->
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" required><?php echo htmlspecialchars($user_profile->address); ?></textarea>
            </div>

            <!-- CV Path (File Input) -->
            <div class="mb-3">
                <label for="cv_path" class="form-label">CV Path</label>
                <input type="file" class="form-control" id="cv_path" name="cv_path">
                <!-- Display the current CV Path -->
                <p>Current CV Path: <?php echo htmlspecialchars($user_profile->cv_path); ?></p>
                <input type="hidden" name="existing_cv_path" value="<?php echo htmlspecialchars($user_profile->cv_path); ?>">


            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    <!-- Bootstrap JS and Popper.js scripts -->
    <script src="<?php echo assets('public/assets/js/bootstrap.bundle.min.js'); ?>"></script>
    <script>
        // Remove the alert after 10 seconds
        setTimeout(function() {
            var alertElement = document.querySelector('.alert');
            if (alertElement) {
                alertElement.style.display = 'none';
            }
        }, 10000);
    </script>
</body>
</html>
