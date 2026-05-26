<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// If Admin, show everything. If User, show only their logs.
if ($role === 'admin') {
    $query = "SELECT l.*, u.username FROM activity_logs l 
              JOIN users u ON l.user_id = u.id 
              ORDER BY l.timestamp DESC";
} else {
    $query = "SELECT l.*, u.username FROM activity_logs l 
              JOIN users u ON l.user_id = u.id 
              WHERE l.user_id = $user_id 
              ORDER BY l.timestamp DESC";
}

$result = $conn->query($query);
?>

<div class="container">
    <h2>System Activity Logs</h2>
    <p>Reviewing all recorded actions for your account.</p>

    <table class="log-table">
        <thead>
            <tr>
                <?php if($role === 'admin'): ?><th>User</th><?php endif; ?>
                <th>Action</th>
                <th>Details</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php while($log = $result->fetch_assoc()): ?>
            <tr>
                <?php if($role === 'admin'): ?>
                    <td><strong><?php echo htmlspecialchars($log['username']); ?></strong></td>
                <?php endif; ?>
                <td><span class="badge"><?php echo htmlspecialchars($log['action']); ?></span></td>
                <td><?php echo htmlspecialchars($log['details']); ?></td>
                <td><?php echo date('M d, Y - H:i', strtotime($log['timestamp'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>