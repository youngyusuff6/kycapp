<?php
session_start();
require_once '../config.php';
require_once '../core/Database.php';
require_once '../core/Session.php';
require_once '../controllers/AuthController.php';

function assets($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}

function handleSignup($authController) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $mobile_phone = $_POST['mobile_phone'];
    $date_of_birth = $_POST['date_of_birth'];
    $address = $_POST['address'];
    $cv_file = $_FILES['cv_path'];

    // Handle file upload
    $cv_path = $authController->handleFileUpload($cv_file);

    $errors = [];

    if (!$cv_path) {
        $errors[] = 'CV upload failed. Please try again.';
    }

    // Check if the email is already registered
    if ($authController->isEmailRegistered($email)) {
        $errors[] = 'Email is already registered. Please use a different email.';
    }

    // Hash the password before storing it
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    if (empty($errors) && $authController->signup($name, $email, $hashedPassword, $mobile_phone, $date_of_birth, $address, $cv_path)) {
        // $successMessage = "Sign up succesful, kindly proceed to login";
        // header('Location: ' . BASE_URL . '/views/login.php?message=' . urlencode($successMessage));
        $_SESSION['success_message'] = 'Sign up successful, kindly proceed to login';
        header('Location: ' . BASE_URL . '/views/login.php');
        exit;

    } elseif (empty($errors)) {
        $errors[] = 'Signup failed. Please try again.';
    }

    return $errors;
}

$db = new Database();
$conn = $db->connect();

$session = new Session();
$authController = new AuthController($conn);

// Check for existing user session
if ($session->get('user_id')) {
    header('Location: profile.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'signup') {
    $errors = handleSignup($authController);
}
?>

<!-- Complete Bootstrap signup page -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <!-- Bootstrap CSS link -->
    <link href="<?php echo assets('public/assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Signup</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <?php foreach ($errors as $error): ?>
                    <?php echo $error . '<br>'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        
        <form action="signup.php" method="post" enctype="multipart/form-data">
            <!-- Your form fields here -->
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="mobile_phone" class="form-label">Mobile Phone</label>
                <input type="tel" class="form-control" id="mobile_phone" name="mobile_phone" required>
            </div>
            <div class="mb-3">
                <label for="date_of_birth" class="form-label">Date of Birth</label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="cv_path" class="form-label">Upload CV</label>
                <input type="file" class="form-control" id="cv_path" name="cv_path" accept=".pdf, .doc, .docx" required>
            </div>
            <button type="submit" class="btn btn-primary" name="action" value="signup">Signup</button>
        </form>
        <p>
            Have an account? 
            <a href="<?php echo BASE_URL . '/' .'views/login.php';?>" class="btn btn-primary">Login</a>
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
        }, 5000);
    </script>
</body>
</html>
