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
$user_count_result = $conn->query("SELECT COUNT(id) as total FROM users WHERE role = 'user'");
$user_count = $user_count_result ? $user_count_result->fetch_assoc()['total'] : 0;

$file_count_result = $conn->query("SELECT COUNT(id) as total FROM files");
$file_count = $file_count_result ? $file_count_result->fetch_assoc()['total'] : 0;

$total_storage_result = $conn->query("SELECT SUM(file_size) as total FROM files");
$total_storage = ($total_storage_result && $total_storage_result->num_rows > 0) ? $total_storage_result->fetch_assoc()['total'] : 0;
if (is_null($total_storage)) {
    $total_storage = 0;
}

// Fetch all users
$users_result = $conn->query("SELECT id, username, email, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC");

// Fetch recent activity
$logs_result = $conn->query("
    SELECT l.*, u.username 
    FROM activity_logs l 
    LEFT JOIN users u ON l.user_id = u.id 
    ORDER BY l.timestamp DESC 
    LIMIT 10
");
?>

<div class="container admin-container animate-fade-in">
    <div class="dashboard-header">
        <div>
            <h1>Admin <span class="accent">Control Panel</span></h1>
            <p class="subtitle">System diagnostics and user administration.</p>
        </div>
    </div>

    <!-- Message Alerts -->
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'user_deleted'): ?>
            <div class="alert alert-success animate-slide-in">User and all their files deleted successfully!</div>
        <?php elseif ($_GET['msg'] == 'file_deleted'): ?>
            <div class="alert alert-success animate-slide-in">File deleted successfully!</div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Overview Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card glassmorphism-card animate-scale-up" style="animation-delay: 0.05s;">
            <div class="stat-icon icon-users">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3>Total Active Users</h3>
                <p class="stat-number"><?php echo $user_count; ?></p>
            </div>
        </div>
        
        <div class="stat-card glassmorphism-card animate-scale-up" style="animation-delay: 0.1s;">
            <div class="stat-icon icon-files">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="stat-info">
                <h3>Total Files Stored</h3>
                <p class="stat-number"><?php echo $file_count; ?></p>
            </div>
        </div>
        
        <div class="stat-card glassmorphism-card animate-scale-up" style="animation-delay: 0.15s;">
            <div class="stat-icon icon-storage">
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-info">
                <h3>Total Storage Used</h3>
                <p class="stat-number"><?php echo round($total_storage / 1024 / 1024, 2); ?> <span class="stat-unit">MB</span></p>
            </div>
        </div>
    </div>

    <div class="admin-sections-layout">
        <!-- User Management Card -->
        <div class="card glassmorphism-card admin-section animate-fade-in" style="animation-delay: 0.2s;">
            <div class="card-header">
                <h2><i class="fas fa-users-cog"></i> User Management</h2>
                <span class="badge"><?php echo $users_result ? $users_result->num_rows : 0; ?> Users</span>
            </div>
            
            <?php if ($users_result && $users_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="premium-table">
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
                                <td class="file-name-cell">
                                    <i class="fas fa-user-circle user-avatar-icon"></i>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="date-text"><?php echo date("M d, Y", strtotime($user['created_at'])); ?></span></td>
                                <td>
                                    <a href="../admin/delete_user.php?id=<?php echo $user['id']; ?>" 
                                       class="btn-action btn-delete" onclick="return confirm('WARNING: Are you sure you want to delete this user? This will permanently delete all their files and logs.')">
                                        <i class="fas fa-user-minus"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <p>No active users found on the system.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Logs Card -->
        <div class="card glassmorphism-card admin-section log-section animate-fade-in" style="animation-delay: 0.25s;">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Recent System Activity</h2>
                <span class="badge badge-accent">Live Activity</span>
            </div>
            
            <?php if ($logs_result && $logs_result->num_rows > 0): ?>
                <ul class="activity-log-list">
                    <?php while($log = $logs_result->fetch_assoc()): ?>
                    <li class="log-item">
                        <div class="log-icon-bullet">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="log-content">
                            <p class="log-text">
                                <strong><?php echo $log['username'] ? htmlspecialchars($log['username']) : 'System'; ?>:</strong> 
                                <?php echo htmlspecialchars($log['action']); ?>
                                <?php if (!empty($log['details'])): ?>
                                    <span class="log-details">- <?php echo htmlspecialchars($log['details']); ?></span>
                                <?php endif; ?>
                            </p>
                            <span class="log-time"><i class="far fa-clock"></i> <?php echo date("M d, Y H:i:s", strtotime($log['timestamp'])); ?></span>
                        </div>
                    </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-file-medical-alt"></i>
                    <p>No activity logs found in database.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>