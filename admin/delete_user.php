<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // 1. Get all file paths for this user to delete them from storage
    $file_stmt = $conn->prepare("SELECT file_path FROM files WHERE user_id = ?");
    $file_stmt->bind_param("i", $user_id);
    $file_stmt->execute();
    $result = $file_stmt->get_result();

    while ($file = $result->fetch_assoc()) {
        $full_path = "../uploads/" . $file['file_path'];
        if (file_exists($full_path)) {
            unlink($full_path); // Physical deletion
        }
    }

    // 2. Delete user from database (Cascade deletes files/logs if FK is set)
    $del_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $del_stmt->bind_param("i", $user_id);
    
    if ($del_stmt->execute()) {
        header("Location: ../dashboard/admin_dashboard.php?msg=user_deleted");
    } else {
        echo "Error deleting user.";
    }
}
?>