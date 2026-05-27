<?php
require_once '../includes/auth_check.php';
include '../includes/header.php';
?>

<div class="container files-container animate-fade-in">
    <div class="card glassmorphism-card auth-card upload-card" style="max-width: 600px; margin: 40px auto;">
        <div class="card-header">
            <h2><i class="fas fa-cloud-upload-alt"></i> Upload to CloudStack</h2>
            <p class="subtitle">Securely add documents, images, or archives to your vault.</p>
        </div>

        <div class="upload-zone">
            <i class="fas fa-file-invoice-dollar upload-icon"></i>
            <div class="allowed-formats">
                <span class="format-badge">PDF</span>
                <span class="format-badge">DOCX</span>
                <span class="format-badge">JPG</span>
                <span class="format-badge">PNG</span>
                <span class="format-badge">ZIP</span>
                <p>Maximum allowed file size: <strong>5 MB</strong></p>
            </div>
            
            <form action="files.php" method="POST" enctype="multipart/form-data" class="premium-form">
                <div class="form-group">
                    <div class="file-input-wrapper">
                        <input type="file" name="fileToUpload" id="fileToUpload" required>
                        <label for="fileToUpload" class="btn btn-secondary btn-block file-label">
                            <i class="fas fa-search-plus"></i> Select File from Computer
                        </label>
                    </div>
                </div>
                <button type="submit" name="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-upload"></i> Upload File Now
                </button>
            </form>
        </div>
        
        <div class="upload-footer">
            <a href="../dashboard/user_dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
// Dynamic file selection feedback
document.getElementById('fileToUpload').addEventListener('change', function(e) {
    var fileName = e.target.files[0] ? e.target.files[0].name : "Select File from Computer";
    var sizeBytes = e.target.files[0] ? e.target.files[0].size : 0;
    var sizeMB = (sizeBytes / (1024 * 1024)).toFixed(2);
    
    var label = document.querySelector('.file-label');
    if (e.target.files[0]) {
        label.innerHTML = '<i class="fas fa-file-alt"></i> Selected: <strong>' + fileName + '</strong> (' + sizeMB + ' MB)';
        label.classList.add('selected');
        
        if (sizeBytes > 5000000) {
            alert("Warning: This file exceeds the 5MB size limit! Uploading may fail.");
        }
    } else {
        label.innerHTML = '<i class="fas fa-search-plus"></i> Select File from Computer';
        label.classList.remove('selected');
    }
});
</script>

<?php include '../includes/footer.php'; ?>