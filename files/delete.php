<?php
session_start();
require_once '../config/database.php';
require_once '../includes/logger.php';

// 1. Authenticate user
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// 2. Validate input ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../dashboard/user_dashboard.php?error=invalid_file");
    exit();
}

$file_id = intval($_GET['id']);

// 3. Fetch file details to verify ownership and get physical path
$stmt = $conn->prepare("SELECT user_id, filename, file_path FROM files WHERE id = ?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../dashboard/user_dashboard.php?error=file_not_found");
    exit();
}

$file = $result->fetch_assoc();

// 4. Security Check: Only the file owner or an Admin can delete this file
if ($file['user_id'] !== $user_id && $user_role !== 'admin') {
    header("Location: ../dashboard/user_dashboard.php?error=unauthorized_deletion");
    exit();
}

// 5. Delete physically from filesystem
$physical_path = "../uploads/" . $file['file_path'];
if (file_exists($physical_path)) {
    if (!unlink($physical_path)) {
        header("Location: ../dashboard/user_dashboard.php?error=file_system_error");
        exit();
    }
}

// 6. Delete database entry (Foreign key cascade automatically handles file_shares)
$del_stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
$del_stmt->bind_param("i", $file_id);

if ($del_stmt->execute()) {
    // 7. Log Activity
    logActivity($conn, $user_id, "File Deleted", "User deleted file: " . $file['filename'] . " (ID: " . $file_id . ")");
    
    // Redirect based on role (admins can delete from their panel, but they usually delete users; if they delete a file, return them back)
    if ($user_role === 'admin' && strpos($_SERVER['HTTP_REFERER'], 'admin_dashboard') !== false) {
        header("Location: ../dashboard/admin_dashboard.php?msg=file_deleted");
    } else {
        header("Location: ../dashboard/user_dashboard.php?status=delete_success");
    }
} else {
    header("Location: ../dashboard/user_dashboard.php?error=database_error");
}
exit();
?>