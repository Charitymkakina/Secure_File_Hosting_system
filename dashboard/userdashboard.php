<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM files WHERE user_id = $user_id");
?>

<div class="container">
    <h2>Your Cloud Files</h2>
    <a href="../files/upload_form.php" class="btn">Upload New File</a>
    <table>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Size (KB)</th>
            <th>Actions</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['filename']; ?></td>
            <td><?php echo $row['file_type']; ?></td>
            <td><?php echo round($row['file_size'] / 1024, 2); ?></td>
            <td>
                <a href="../files/download.php?id=<?php echo $row['id']; ?>">Download</a>
                <a href="../files/delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include '../includes/footer.php'; ?>