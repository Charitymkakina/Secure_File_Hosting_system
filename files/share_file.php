<?php
session_start();
require_once '../config/database.php';
require_once '../includes/logger.php';

// 1. Authenticate user session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['file_id']) || !isset($_POST['recipient_id'])) {
    header("Location: ../dashboard/user_dashboard.php?error=invalid_request");
    exit();
}

$file_id = intval($_POST['file_id']);
$recipient_id = intval($_POST['recipient_id']);
$permission = ($_POST['permission'] === 'download') ? 'download' : 'view';

// 3. Security Check: Verify that the current user owns the file to be shared
$file_stmt = $conn->prepare("SELECT filename FROM files WHERE id = ? AND user_id = ?");
$file_stmt->bind_param("ii", $file_id, $user_id);
$file_stmt->execute();
$file_result = $file_stmt->get_result();

if ($file_result->num_rows === 0) {
    header("Location: ../dashboard/user_dashboard.php?error=unauthorized_share_action");
    exit();
}

$file = $file_result->fetch_assoc();

// 4. Security Check: Prevent self-sharing
if ($recipient_id === $user_id) {
    header("Location: ../dashboard/user_dashboard.php?error=cannot_share_with_self");
    exit();
}

// 5. Verify recipient exists
$recip_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$recip_stmt->bind_param("i", $recipient_id);
$recip_stmt->execute();
$recip_result = $recip_stmt->get_result();

if ($recip_result->num_rows === 0) {
    header("Location: ../dashboard/user_dashboard.php?error=recipient_not_found");
    exit();
}

$recipient = $recip_result->fetch_assoc();

// 6. Handle Duplicates: Check if the file is already shared with this user
$check_share = $conn->prepare("SELECT id FROM file_shares WHERE file_id = ? AND shared_with = ?");
$check_share->bind_param("ii", $file_id, $recipient_id);
$check_share->execute();
$check_result = $check_share->get_result();

if ($check_result->num_rows > 0) {
    // Elegant fix: Update permission instead of failing
    $update_stmt = $conn->prepare("UPDATE file_shares SET permission = ? WHERE file_id = ? AND shared_with = ?");
    $update_stmt->bind_param("sii", $permission, $file_id, $recipient_id);
    
    if ($update_stmt->execute()) {
        logActivity($conn, $user_id, "File Share Updated", "Updated permissions for file '" . $file['filename'] . "' shared with " . $recipient['username'] . " to " . $permission);
        header("Location: ../dashboard/user_dashboard.php?status=share_success");
    } else {
        header("Location: ../dashboard/user_dashboard.php?error=share_update_failed");
    }
} else {
    // Insert new share
    $insert_stmt = $conn->prepare("INSERT INTO file_shares (file_id, shared_by, shared_with, permission) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("iiis", $file_id, $user_id, $recipient_id, $permission);
    
    if ($insert_stmt->execute()) {
        logActivity($conn, $user_id, "File Shared", "Shared file '" . $file['filename'] . "' with " . $recipient['username'] . " (Permission: " . $permission . ")");
        header("Location: ../dashboard/user_dashboard.php?status=share_success");
    } else {
        header("Location: ../dashboard/user_dashboard.php?error=share_failed");
    }
}
exit();
?>