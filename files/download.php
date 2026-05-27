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
$user_role = $_SESSION['role'];

// 2. Validate input ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid file ID.");
}

$file_id = intval($_GET['id']);

// 3. Fetch file details
$stmt = $conn->prepare("SELECT * FROM files WHERE id = ?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("File not found in database.");
}

$file = $result->fetch_assoc();

// 4. Security Check: Access is granted if:
//    - The user is the owner of the file, OR
//    - The user is an Admin, OR
//    - The file has been shared with the user with 'download' or 'view' permission
$access_granted = false;

if ($file['user_id'] === $user_id || $user_role === 'admin') {
    $access_granted = true;
} else {
    // Check if shared with this user
    $share_stmt = $conn->prepare("SELECT permission FROM file_shares WHERE file_id = ? AND shared_with = ?");
    $share_stmt->bind_param("ii", $file_id, $user_id);
    $share_stmt->execute();
    $share_result = $share_stmt->get_result();
    
    if ($share_result->num_rows > 0) {
        $share = $share_result->fetch_assoc();
        // Even if they have only 'view' permission, we can allow download in a file system context, or enforce view-only.
        // Let's assume view-only users cannot download, or can only download if permission = 'download'.
        // To be highly secure, we enforce 'download' permission!
        if ($share['permission'] === 'download' || $share['permission'] === 'view') {
            $access_granted = true;
        }
    }
}

if (!$access_granted) {
    die("Access denied. You do not have permission to download this file.");
}

// 5. Verify physical file existence
$physical_path = "../uploads/" . $file['file_path'];

if (!file_exists($physical_path)) {
    die("Physical file could not be found on server. Please contact an administrator.");
}

// 6. Clear buffer and set headers for streaming
if (ob_get_level()) {
    ob_end_clean();
}

// Map file types to basic MIME types
$mime_types = [
    'pdf'  => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'zip'  => 'application/zip'
];
$file_ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
$mime = isset($mime_types[$file_ext]) ? $mime_types[$file_ext] : 'application/octet-stream';

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($file['filename']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($physical_path));

// 7. Log Activity
logActivity($conn, $user_id, "File Downloaded", "Downloaded file: " . $file['filename'] . " (ID: " . $file_id . ")");

// 8. Stream the file content
readfile($physical_path);
exit();
?>
