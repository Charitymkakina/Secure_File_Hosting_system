<?php
session_start();
require_once '../config/database.php';

$message = "";

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
        }
    } else {
        $message = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>CFHMS - Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Login to CloudStack</h2>
        <?php if(isset($_GET['signup'])) echo "<p class='success'>Registration successful! Please login.</p>"; ?>
        <?php if($message) echo "<p class='error'>$message</p>"; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Sign up</a></p>
    </div>
</body>
</html>