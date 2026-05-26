<?php
// We include the database config here to ensure the connection is available
require_once __DIR__ . '/../config/database.php';

/**
 * Logs a user action into the database.
 * * @param mysqli $conn The database connection object
 * @param int $user_id The ID of the user performing the action
 * @param string $action The title of the action (e.g., 'File Upload')
 * @param string $details Detailed description (e.g., 'Uploaded file: document.pdf')
 */
function logActivity($conn, $user_id, $action, $details = "") {
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $action, $details);
    return $stmt->execute();
}
?>
