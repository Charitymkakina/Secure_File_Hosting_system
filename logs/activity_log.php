<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// If Admin, show everything. If User, show only their logs.
if ($role === 'admin') {
    $query = "SELECT l.*, u.username FROM activity_logs l 
              LEFT JOIN users u ON l.user_id = u.id 
              ORDER BY l.timestamp DESC";
} else {
    $query = "SELECT l.*, u.username FROM activity_logs l 
              LEFT JOIN users u ON l.user_id = u.id 
              WHERE l.user_id = $user_id 
              ORDER BY l.timestamp DESC";
}

$result = $conn->query($query);
?>

<div class="container animate-fade-in">
    <div class="dashboard-header">
        <div>
            <h1>System <span class="accent">Activity Logs</span></h1>
            <p class="subtitle">Review a chronological record of actions performed on this account.</p>
        </div>
    </div>

    <div class="card glassmorphism-card animate-fade-in" style="animation-delay: 0.1s;">
        <div class="card-header">
            <h2><i class="fas fa-history"></i> Recorded Events</h2>
            <span class="badge"><?php echo $result ? $result->num_rows : 0; ?> Log Entries</span>
        </div>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="premium-table">
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
                                <td class="file-name-cell">
                                    <i class="fas fa-user-circle user-avatar-icon"></i>
                                    <strong><?php echo $log['username'] ? htmlspecialchars($log['username']) : 'System'; ?></strong>
                                </td>
                            <?php endif; ?>
                            <td>
                                <span class="badge <?php echo strpos(strtolower($log['action']), 'delete') !== false ? 'badge-accent' : ''; ?>">
                                    <?php echo htmlspecialchars($log['action']); ?>
                                </span>
                            </td>
                            <td class="date-text" style="color: var(--text-main);"><?php echo htmlspecialchars($log['details'] ?? 'No extra details recorded.'); ?></td>
                            <td><span class="date-text"><i class="far fa-clock"></i> <?php echo date('M d, Y - H:i:s', strtotime($log['timestamp'])); ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <p>No activity logs have been recorded yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>