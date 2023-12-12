<?php
session_start();

require_once 'config.php';
require_once 'core/Database.php';
require_once 'core/Session.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once 'controllers/EditProfileController.php';

$db = new Database();
$conn = $db->connect();

$session = new Session();

$authController = new AuthController($conn);
$editProfileController = new EditProfileController($conn);

// Check for existing user session
if ($session->get('user_id')) {
    header('Location: views/profile.php');
    exit;
}

// Route handling
///




// Display the appropriate view based on the user's state (logged in or not)
if ($session->get('user_id')) {
    header('Location: ' . BASE_URL . '/views/profile.php');
    exit;
} else {
    header('Location: ' . BASE_URL . '/views/login.php');
    exit;
}

?>
