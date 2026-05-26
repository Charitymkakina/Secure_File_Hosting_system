<?php
require_once '../includes/auth_check.php';
include '../includes/header.php';
?>

<div class="container">
    <div class="upload-box">
        <h2>Upload File to CloudStack</h2>
        <p>Allowed formats: PDF, DOCX, JPG, PNG, ZIP (Max 5MB)</p>
        
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <input type="file" name="fileToUpload" id="fileToUpload" required>
            </div>
            <button type="submit" name="submit" class="btn">Upload Now</button>
        </form>
        <br>
        <a href="../dashboard/user_dashboard.php">Back to Dashboard</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>