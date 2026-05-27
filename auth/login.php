<?php
session_start();
require_once '../config/database.php';

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            // Set Session Variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Log activity
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, 'User Login')");
            $log_stmt->bind_param("i", $user['id']);
            $log_stmt->execute();

            // Redirect based on role
            if ($user['role'] == 'admin') {
                header("Location: ../dashboard/admin_dashboard.php");
            } else {
                header("Location: ../dashboard/user_dashboard.php");
            }
            exit();
        } else {
            $message = "Invalid password.";
            $message_type = "error";
        }
    } else {
        $message = "User not found.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CloudStack - Sign In</title>
    <!-- Fonts and Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-body">
    <div class="auth-wrapper animate-fade-in">
        <div class="auth-brand">
            <i class="fas fa-layer-group"></i>
            <h1>Cloud<span>Stack</span></h1>
            <p>Secure File Hosting & Collaboration</p>
        </div>
        <div class="card auth-card">
            <h2>Welcome Back</h2>
            <p class="auth-subtitle">Sign in to access your secure file vault.</p>
            
            <?php if(isset($_GET['signup']) && $_GET['signup'] == 'success'): ?>
                <div class="alert alert-success animate-slide-in">
                    <i class="fas fa-check-circle"></i> Registration successful! Please login.
                </div>
            <?php endif; ?>

            <?php if($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> animate-slide-in">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Sign up now</a></p>
            </div>
        </div>
    </div>
</body>
</html>