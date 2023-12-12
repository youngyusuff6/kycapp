<?php
require_once '../config.php';
require_once '../core/Session.php';
require_once '../core/Database.php';
require_once '../controllers/AuthController.php';

function assets($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Start the session
session_start();

// Retrieve the success message from the session
$message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);

$db = new Database();
$conn = $db->connect();

// Create an instance of the Session class
$session = new Session();

// Create an instance of the AuthController class
$authController = new AuthController($conn);

// Check for an existing user session
if ($session->get('user_id')) {
    header('Location: profile.php');
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    handleLogin($authController);
}

function handleLogin($authController) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($authController->login($email, $password)) {
        header('Location: profile.php');
        exit;
    } else {
        // Set an error message if login fails
        $_SESSION['error_message'] = 'Invalid email or password. Please try again.';
        header('Location: login.php');
        exit;
    }
}

?>

<!-- Bootstrap form for user login -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS link -->
    <link href="<?php echo assets('public/assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Login</h2>

        <?php
        $errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

        if (!empty($errorMessage)) {
            echo '<div class="alert alert-danger" role="alert">' . $errorMessage . '</div>';
            unset($_SESSION['error_message']); // Clear the error message after displaying it
        }

        if (!empty($message)) {
            echo '<div class="alert alert-success" role="alert">' . $message . '</div>';
        }   
        ?>

            <form action="login.php" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary" name="action" value="login">Login</button>
            </form>

        <p>
            New user? 
            <a href="<?php echo BASE_URL . '/' .'views/signup.php';?>" class="btn btn-primary">Sign up</a>
        </p>
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
