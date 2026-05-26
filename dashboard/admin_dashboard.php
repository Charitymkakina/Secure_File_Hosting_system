<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
include '../includes/header.php';

// Security: Check if the user is actually an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit();
}

// Fetch stats for the overview cards
$user_count = $conn->query("SELECT COUNT(id) as total FROM users WHERE role = 'user'")->fetch_assoc()['total'];
$file_count = $conn->query("SELECT COUNT(id) as total FROM files")->fetch_assoc()['total'];
$total_storage = $conn->query("SELECT SUM(file_size) as total FROM files")->fetch_assoc()['total'];

// Fetch all users
$users_result = $conn->query("SELECT id, username, email, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC");

// Fetch recent activity
$logs_result = $conn->query("SELECT l.*, u.username FROM activity_logs l JOIN users u ON l.user_id = u.id ORDER BY l.timestamp DESC LIMIT 10");
?>

<div class="container admin-container">
    <h1>Admin Control Panel</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Users</h3>
            <p><?php echo $user_count; ?></p>
        </div>
        <div class="stat-card">
            <h3>Files Stored</h3>
            <p><?php echo $file_count; ?></p>
        </div>
        <div class="stat-card">
            <h3>Storage Used</h3>
            <p><?php echo round($total_storage / 1024 / 1024, 2); ?> MB</p>
        </div>
    </div>

    <hr>

    <div class="admin-sections">
        <section>
            <h2>User Management</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo $user['created_at']; ?></td>
                        <td>
                            <a href="../admin/delete_user.php?id=<?php echo $user['id']; ?>" 
                               class="btn-danger" onclick="return confirm('Delete user and all their files?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <section>
            <h2>Recent System Activity</h2>
            <ul class="log-list">
                <?php while($log = $logs_result->fetch_assoc()): ?>
                <li>
                    <strong><?php echo $log['username']; ?>:</strong> 
                    <?php echo $log['action']; ?> 
                    <small>(<?php echo $log['timestamp']; ?>)</small>
                </li>
                <?php endwhile; ?>
            </ul>
        </section>
    </div>
</div>

<?php include '../includes/footer.php'; ?>