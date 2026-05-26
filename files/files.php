<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $target_dir = "../uploads/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $original_name = basename($_FILES["fileToUpload"]["name"]);
    $file_type = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    
    // Security: Generate a unique name to prevent overwriting and directory traversal
    $unique_name = time() . "_" . bin2hex(random_bytes(8)) . "." . $file_type;
    $target_file = $target_dir . $unique_name;
    
    $file_size = $_FILES["fileToUpload"]["size"];
    $allowed_types = ['pdf', 'docx', 'jpg', 'png', 'zip'];

    // 1. Check file extension
    if (!in_array($file_type, $allowed_types)) {
        die("Error: Only PDF, DOCX, JPG, PNG, and ZIP files are allowed.");
    }

    // 2. Check file size (5MB limit)
    if ($file_size > 5000000) {
        die("Error: File is too large. Maximum size is 5MB.");
    }

    // 3. Process Upload
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        
        // Save metadata to database
        $stmt = $conn->prepare("INSERT INTO files (user_id, filename, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $user_id, $original_name, $unique_name, $file_type, $file_size);
        
        if ($stmt->execute()) {
            // Log the activity
            $action = "File Upload";
            $details = "Uploaded: " . $original_name;
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $user_id, $action, $details);
            $log_stmt->execute();

            header("Location: ../dashboard/user_dashboard.php?status=upload_success");
        } else {
            echo "Database error: Could not save file info.";
        }
    } else {
        echo "Error: There was an issue moving the uploaded file.";
    }
} else {
    header("Location: upload_form.php");
}
?>