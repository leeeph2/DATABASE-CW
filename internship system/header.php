<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Management | University of Nottingham</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar-global">
        <div class="nav-container">
            <div class="nav-logo">
                <span class="stat-label">System Portal</span>
                
                <div class="nav-brand">
                    <div class="nav-brand-icon-wrap">
                        <div class="nav-brand-icon-box">
                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                                <line x1="5"  y1="18" x2="5"  y2="4"  stroke="white" stroke-width="2.8" stroke-linecap="round"/>
                                <line x1="5"  y1="4"  x2="13" y2="18" stroke="white" stroke-width="2.8" stroke-linecap="round"/>
                                <line x1="13" y1="18" x2="13" y2="4"  stroke="white" stroke-width="2.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="nav-brand-dot"></div>
                    </div>

                    <div class="nav-brand-text">
                        <div class="nav-brand-title">NOTT</div>
                        <div class="nav-brand-subtitle">INTERN</div>
                    </div>
                </div>

            </div>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="admin_dashboard.php">Dashboard</a>
                    <a href="logout.php" class="logout-link">Sign Out</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">