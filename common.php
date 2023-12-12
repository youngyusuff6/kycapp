<?php
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Check for inactivity and logout if needed
$inactive = 300; // 5 minutes
if (isset($_SESSION['timeout']) && time() - $_SESSION['timeout'] > $inactive) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Update the session timeout on activity
$_SESSION['timeout'] = time();
?>
