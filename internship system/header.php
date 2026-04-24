<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CENTRALIZED NOTIFICATION SYSTEM 
$show_notification = false;
$notif_class = "";
$notif_text = "";

// Handle Error Messages
if (isset($_GET['error'])) {
    $show_notification = true;
    $notif_class = "error-notification";
    
    if ($_GET['error'] === 'unauthorized') {
        $notif_text = "Access Denied: You are not authorized to view this page.";
    } else {
        $notif_text = "System Error: An unexpected error occurred.";
    }
} 
// Handle Success Messages
elseif (isset($_GET['msg'])) {
    $show_notification = true;
    $notif_class = "success-notification";
    $msg = $_GET['msg'];
    
    switch ($msg) {
        case 'evaluated':
            $name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Student';
            $notif_text = "Success: Evaluation for '$name' has been submitted successfully!";
            break;
        case 'registered':
            $name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Assessor';
            $notif_text = "Success: Assessor '$name' registered successfully.";
            break;
        case 'added':
            $name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Student';
            $notif_text = "Success: New student '$name' registered successfully.";
            break;
        case 'updated':
            $name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Record';
            $notif_text = "Success: Record for '$name' updated successfully.";
            break;
        case 'deleted':
            $name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Record';
            $notif_text = "Success: Student '$name' deleted successfully.";
            break;
        case 'removed':
            $name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Record';
            $notif_text = "Success: $name removed successfully.";
            break;
        default:
            $notif_text = "Success: Action completed successfully.";
            break;
    }

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
                <div style="display:flex; align-items:center; gap:10px; text-decoration:none;">
    <!-- Square icon -->
    <div style="position:relative; width:36px; height:36px; flex-shrink:0;">
        <div style="width:36px; height:36px; background:#1e3a8a; border-radius:8px; display:flex; align-items:center; justify-content:center;">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                <line x1="5"  y1="18" x2="5"  y2="4"  stroke="white" stroke-width="2.8" stroke-linecap="round"/>
                <line x1="5"  y1="4"  x2="13" y2="18" stroke="white" stroke-width="2.8" stroke-linecap="round"/>
                <line x1="13" y1="18" x2="13" y2="4"  stroke="white" stroke-width="2.8" stroke-linecap="round"/>
            </svg>
        </div>
        <!-- Accent dot -->
        <div style="position:absolute; top:-3px; right:-3px; width:9px; height:9px; background:#2563eb; border-radius:50%; border:2px solid var(--card-white, #fff);"></div>
    </div>

    <!-- Stacked text -->
    <div style="line-height:1.1;">
        <div style="font-size:0.85rem; font-weight:800; letter-spacing:0.18em; color:var(--primary-dark, #1e3a8a);">NOTT</div>
        <div style="font-size:0.85rem; font-weight:800; letter-spacing:0.18em; color:var(--primary-blue, #2563eb);">INTERN</div>
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

    <?php if ($show_notification && $notif_text !== ""): ?>
        <div class="system-notification <?php echo $notif_class; ?>">
            <?php echo $notif_text; ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-container">