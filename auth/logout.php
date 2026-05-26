<?php
session_start();

// Optionally log the logout event before destroying the session
require_once '../config/database.php';
if (isset($_SESSION['user_id'])) {
    $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, 'User Logout')");
    $log_stmt->bind_param("i", $_SESSION['user_id']);
    $log_stmt->execute();
}

session_unset();
session_destroy();
header("Location: login.php");
exit();
?>