<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Get current page name to highlight active link
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CloudStack - Secure File Hosting</title>
    <!-- Modern Typography and Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Premium Stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="app-header">
        <div class="header-container">
            <div class="header-brand">
                <i class="fas fa-layer-group"></i>
                <span class="brand-text">Cloud<span class="accent">Stack</span></span>
            </div>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <nav class="header-nav">
                    <!-- Standard Navigation -->
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="../dashboard/admin_dashboard.php" class="nav-link <?php echo $current_page == 'admin_dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i> Admin Panel
                        </a>
                    <?php endif; ?>
                    
                    <a href="../dashboard/user_dashboard.php" class="nav-link <?php echo $current_page == 'user_dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-folder"></i> My Files
                    </a>
                    
                    <a href="../files/upload_form.php" class="nav-link <?php echo $current_page == 'upload_form.php' ? 'active' : ''; ?>">
                        <i class="fas fa-cloud-upload-alt"></i> Upload
                    </a>
                    
                    <a href="../logs/activity_log.php" class="nav-link <?php echo $current_page == 'activity_log.php' ? 'active' : ''; ?>">
                        <i class="fas fa-history"></i> Activity Log
                    </a>
                </nav>
                
                <div class="header-profile">
                    <span class="user-pill">
                        <i class="fas fa-user-circle"></i>
                        <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <span class="role-badge <?php echo $_SESSION['role'] == 'admin' ? 'role-admin' : 'role-user'; ?>">
                            <?php echo strtoupper($_SESSION['role']); ?>
                        </span>
                    </span>
                    <a href="../auth/logout.php" class="btn btn-logout" title="Log Out">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <main class="app-main-content">
