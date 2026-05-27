<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// 1. Validate file ID and verify ownership
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../dashboard/user_dashboard.php?error=invalid_file");
    exit();
}

$file_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT filename FROM files WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../dashboard/user_dashboard.php?error=unauthorized_share");
    exit();
}

$file = $result->fetch_assoc();

// 2. Fetch other users on the platform to share with
$users_stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id != ? ORDER BY username ASC");
$users_stmt->bind_param("i", $user_id);
$users_stmt->execute();
$users_result = $users_stmt->get_result();
?>

<div class="container files-container animate-fade-in">
    <div class="card glassmorphism-card auth-card share-card" style="max-width: 600px; margin: 40px auto;">
        <div class="card-header">
            <h2><i class="fas fa-share-alt"></i> Share File</h2>
            <p class="subtitle">Grant access to other members of the platform.</p>
        </div>
        
        <div class="share-file-info">
            <span class="info-label">File to Share:</span>
            <span class="info-value"><i class="far fa-file-alt"></i> <?php echo htmlspecialchars($file['filename']); ?></span>
        </div>

        <form action="share_file.php" method="POST" class="premium-form">
            <!-- Hidden inputs to pass file details -->
            <input type="hidden" name="file_id" value="<?php echo $file_id; ?>">

            <div class="form-group">
                <label for="recipient_id"><i class="fas fa-user-plus"></i> Select Recipient User</label>
                <div class="select-wrapper">
                    <select name="recipient_id" id="recipient_id" required>
                        <option value="" disabled selected>Choose a user...</option>
                        <?php if ($users_result->num_rows > 0): ?>
                            <?php while ($user = $users_result->fetch_assoc()): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="" disabled>No other users found on the system.</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="permission"><i class="fas fa-shield-alt"></i> Sharing Permissions</label>
                <div class="select-wrapper">
                    <select name="permission" id="permission" required>
                        <option value="view">View Only (Online Reading/Preview)</option>
                        <option value="download" selected>Download Allowed (Full access to save file)</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <a href="../dashboard/user_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <button type="submit" name="submit" class="btn btn-primary" <?php echo $users_result->num_rows === 0 ? 'disabled' : ''; ?>>
                    <i class="fas fa-paper-plane"></i> Share Now
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
