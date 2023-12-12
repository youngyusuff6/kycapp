<?php
// Include necessary files and initialize objects
session_start();
require_once '../config.php';
require_once '../core/Session.php';
require_once '../core/Database.php';
require_once '../models/User.php';
require_once '../models/User_Profile.php';
require_once '../controllers/AuthController.php';
require_once '../common.php';

function assets($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Check for an active user session
$session = new Session();
$user_id = $session->get('user_id');
if (!$user_id) {
    header('Location: index.php'); // Redirect to the index page if no active session
    exit;
}

// Create instances of necessary classes
$db = new Database();
$conn = $db->connect();
$user = new User($conn);

// Fetch user data
$user->id = $user_id;
$user->readById(); // Add this line to fetch user data

// Fetch user profile for display
$user_profile = new User_Profile($conn);
$user_profile->user_id = $user_id;
$user_profile->readByUserId();
//Initialize auth controller
$authController = new AuthController($conn);
// Check if the logout link is clicked
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    handleLogout($authController);
}elseif(isset($_GET['action']) && $_GET['action'] === 'delete') {
    handleProfileDeletion($authController);
}

function handleProfileDeletion($authController) {
    // Fetch user ID
    $user_id = $_SESSION['user_id'] ?? null;

    // Check for an active user session
    if (!$user_id) {
        header('Location: ' . BASE_URL . '/index.php'); // Redirect to the index page
        exit;
    }

    // Delete the user profile
    if ($authController->deleteUserProfile($user_id) && $authController->deleteUser($user_id)) {
        // Logout the user after deletion
        $authController->logout();
        $_SESSION['success_message'] = 'Your profile has been successfully deleted.';
        header('Location: ' . BASE_URL . '/index.php');
    } else {
        $_SESSION['error_message'] = 'Failed to delete profile. Please try again.';
    }

    header('Location: ' . BASE_URL . '/views/profile.php');
    $_SESSION['error_message'] = 'Failed to delete profile. Please try again.';
    exit;
}

// Function to handle logout
function handleLogout($authController) {
    $authController->logout();
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}
?>

<!-- Display user profile information -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
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
        <h2>User Profile</h2>
            <?php 
             // Display error message if any
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']); // Clear the message after displaying it
            }
             // Display success message if any
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success" role="alert">' . $_SESSION['success_message'] . '</div>';
                unset($_SESSION['success_message']); // Clear the message after displaying it
            }
            ?>
        <!-- Display user information -->
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($user->name); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user->email); ?>" readonly>
        </div>

        <!-- Display user profile information -->
        <div class="mb-3">
            <label for="mobile_phone" class="form-label">Mobile Phone</label>
            <input type="text" class="form-control" id="mobile_phone" value="<?php echo htmlspecialchars($user_profile->mobile_phone); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="date_of_birth" class="form-label">Date of Birth</label>
            <input type="text" class="form-control" id="date_of_birth" value="<?php echo htmlspecialchars($user_profile->date_of_birth); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" readonly><?php echo htmlspecialchars($user_profile->address); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="cv_path" class="form-label">CV Path</label>
            <input type="text" class="form-control" id="cv_path" value="<?php echo htmlspecialchars($user_profile->cv_path); ?>" readonly>
        </div>
    </div>

            <div class="text-center">
                <button class="btn btn-danger" onclick="confirmDelete()">Delete Profile</button>
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
        }, 50000);

        function confirmDelete() {
            if (confirm('Are you sure you want to delete your profile? This action cannot be undone.')) {
                window.location.href = '<?php echo BASE_URL; ?>/views/profile.php?action=delete'; // Redirect to handle deletion in the same file
            }
        }
    </script>
</body>
</html>
