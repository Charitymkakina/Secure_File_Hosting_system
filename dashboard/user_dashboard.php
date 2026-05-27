<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Fetch user's own files
$files_query = $conn->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY uploaded_at DESC");
$files_query->bind_param("i", $user_id);
$files_query->execute();
$files_result = $files_query->get_result();

// Fetch files shared with this user
$shares_query = $conn->prepare("
    SELECT f.id as file_id, f.filename, f.file_type, f.file_size, u.username as owner_name, fs.permission, fs.shared_at 
    FROM file_shares fs
    JOIN files f ON fs.file_id = f.id
    JOIN users u ON fs.shared_by = u.id
    WHERE fs.shared_with = ?
    ORDER BY fs.shared_at DESC
");
$shares_query->bind_param("i", $user_id);
$shares_query->execute();
$shares_result = $shares_query->get_result();
?>

<div class="container dashboard-container">
    <div class="dashboard-header animate-fade-in">
        <div>
            <h1>Welcome back, <span class="accent"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h1>
            <p class="subtitle">Manage your secure cloud vault and files.</p>
        </div>
        <a href="../files/upload_form.php" class="btn btn-primary btn-with-icon">
            <i class="fas fa-cloud-upload-alt"></i> Upload New File
        </a>
    </div>

    <!-- Upload status messages -->
    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] == 'upload_success'): ?>
            <div class="alert alert-success animate-slide-in">File uploaded successfully!</div>
        <?php elseif ($_GET['status'] == 'delete_success'): ?>
            <div class="alert alert-success animate-slide-in">File deleted successfully!</div>
        <?php elseif ($_GET['status'] == 'share_success'): ?>
            <div class="alert alert-success animate-slide-in">File shared successfully!</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger animate-slide-in"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <!-- User's own files section -->
    <div class="card glassmorphism-card animate-fade-in" style="animation-delay: 0.1s;">
        <div class="card-header">
            <h2><i class="fas fa-folder-open"></i> Your Stored Files</h2>
            <span class="badge"><?php echo $files_result->num_rows; ?> Files</span>
        </div>
        
        <?php if ($files_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $files_result->fetch_assoc()): ?>
                        <tr>
                            <td class="file-name-cell">
                                <i class="far fa-file file-icon type-<?php echo $row['file_type']; ?>"></i>
                                <span><?php echo htmlspecialchars($row['filename']); ?></span>
                            </td>
                            <td><span class="type-badge"><?php echo strtoupper($row['file_type']); ?></span></td>
                            <td><?php echo round($row['file_size'] / 1024, 2); ?> KB</td>
                            <td><span class="date-text"><?php echo date("M d, Y H:i", strtotime($row['uploaded_at'])); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="../files/download.php?id=<?php echo $row['id']; ?>" class="btn-action btn-download" title="Download">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <a href="../files/share.php?id=<?php echo $row['id']; ?>" class="btn-action btn-share" title="Share">
                                        <i class="fas fa-share-alt"></i> Share
                                    </a>
                                    <a href="../files/delete.php?id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this file permanently?')" title="Delete">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-cloud"></i>
                <p>No files uploaded yet. Start by uploading your first document!</p>
                <a href="../files/upload_form.php" class="btn btn-secondary">Upload File</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Shared with me section -->
    <div class="card glassmorphism-card animate-fade-in" style="animation-delay: 0.2s; margin-top: 40px;">
        <div class="card-header">
            <h2><i class="fas fa-user-friends"></i> Files Shared With You</h2>
            <span class="badge badge-accent"><?php echo $shares_result->num_rows; ?> Shared</span>
        </div>
        
        <?php if ($shares_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Shared By</th>
                            <th>Size</th>
                            <th>Shared Date</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $shares_result->fetch_assoc()): ?>
                        <tr>
                            <td class="file-name-cell">
                                <i class="far fa-file file-icon type-<?php echo $row['file_type']; ?>"></i>
                                <span><?php echo htmlspecialchars($row['filename']); ?></span>
                            </td>
                            <td><span class="user-badge"><i class="fas fa-user"></i> <?php echo htmlspecialchars($row['owner_name']); ?></span></td>
                            <td><?php echo round($row['file_size'] / 1024, 2); ?> KB</td>
                            <td><span class="date-text"><?php echo date("M d, Y H:i", strtotime($row['shared_at'])); ?></span></td>
                            <td>
                                <span class="permission-tag <?php echo $row['permission'] == 'download' ? 'perm-download' : 'perm-view'; ?>">
                                    <?php echo strtoupper($row['permission']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($row['permission'] == 'download'): ?>
                                        <a href="../files/download.php?id=<?php echo $row['file_id']; ?>" class="btn-action btn-download">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    <?php else: ?>
                                        <span class="btn-action btn-disabled" title="You only have view permission for this file">
                                            <i class="fas fa-eye"></i> View Only
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-user-shield"></i>
                <p>No files have been shared with you yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
