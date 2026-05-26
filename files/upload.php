<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

if (isset($_POST['submit'])) {
    $target_dir = "../uploads/";
    $file_name = basename($_FILES["fileToUpload"]["name"]);
    $target_file = $target_dir . time() . "_" . $file_name; // Rename to prevent overwriting
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['pdf', 'docx', 'jpg', 'png', 'zip'];

    if (in_array($fileType, $allowed_types) && $_FILES["fileToUpload"]["size"] < 5000000) {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO files (user_id, filename, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $_SESSION['user_id'], $file_name, $target_file, $fileType, $_FILES["fileToUpload"]["size"]);
            $stmt->execute();
            
            // Log Activity
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, 'Uploaded file')");
            $log_stmt->bind_param("i", $_SESSION['user_id']);
            $log_stmt->execute();

            header("Location: ../dashboard/user_dashboard.php?upload=success");
        }
    } else {
        echo "Invalid file type or file too large.";
    }
}
?>