<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CENTRALIZED NOTIFICATION SYSTEM 
$show_notification = false;
$notif_class = "";
$notif_text = "";

if (isset($_GET['error'])) {
    $show_notification = true;
    $notif_class = "error-notification";
    $notif_text = ($_GET['error'] === 'unauthorized') ? "Access Denied: Unauthorized view." : "System Error occurred.";
} elseif (isset($_GET['msg'])) {
    $show_notification = true;
    $notif_class = "success-notification";
    $notif_text = "Success: Action completed successfully.";
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
    <style>
        .nav-right-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-icon-group {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-right: 15px;
            border-right: 1.5px solid #e2e8f0;
        }

        .nav-icon-link {
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }

        .nav-icon-link:hover {
            transform: translateY(-2px);
            opacity: 0.8;
        }

        .nav-icon-link img {
            width: 22px;
            height: 22px;
            object-fit: contain;
        }

        .logout-btn-styled {
            color: #ef4444;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 6px 12px;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .logout-btn-styled:hover {
            background: #fef2f2;
        }
    </style>
</head>
<body>
    <nav class="navbar-global">
        <div class="nav-container" style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            
            <div class="nav-logo">
                <a href="index.php" style="display:flex; align-items:center; gap:12px; text-decoration: none;">
                    <div style="position:relative; width:36px; height:36px;">
                        <div style="width:36px; height:36px; background:#1e3a8a; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                            <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                                <line x1="5" y1="18" x2="5" y2="4" stroke="white" stroke-width="2.8" stroke-linecap="round"/>
                                <line x1="5" y1="4" x2="13" y2="18" stroke="white" stroke-width="2.8" stroke-linecap="round"/>
                                <line x1="13" y1="18" x2="13" y2="4" stroke="white" stroke-width="2.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div style="position:absolute; top:-3px; right:-3px; width:9px; height:9px; background:#2563eb; border-radius:50%; border:2px solid #fff;"></div>
                    </div>
                    <div style="line-height:1.1;">
                        <div style="font-size:0.85rem; font-weight:800; letter-spacing:0.15em; color:#1e3a8a;">NOTT</div>
                        <div style="font-size:0.85rem; font-weight:800; letter-spacing:0.15em; color:#2563eb;">INTERN</div>
                    </div>
                </a>
            </div>

            <div class="nav-right-section">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="header-icon-group">
                        
                        <a href="#" class="nav-icon-link" title="My Profile">
                            <img src="images/profile.png" alt="Profile">
                        </a>
                        
                    </div>

                    <div class="nav-links" style="display: flex; align-items: center; gap: 15px;">
                        <?php 
                            $role = $_SESSION['role'] ?? '';
                            $dashboard_url = match($role) {
                                'Admin' => 'admin_dashboard.php',
                                'Lecturer' => 'lecturer_dashboard.php',
                                'Supervisor' => 'supervisor_dashboard.php',
                                default => 'index.php'
                            };
                        ?>
                        <a href="<?php echo $dashboard_url; ?>" style="font-weight: 600; font-size: 0.9rem;">Dashboard</a>
                        <a href="logout.php" class="logout-btn-styled">Sign Out</a>

                        <a href="#" class="nav-icon-link" title="Settings">
                            <img src="images/settings.png" alt="Settings">
                        </a>
                    </div>
                <?php else: ?>
                    <a href="index.php" class="btn-primary" style="padding: 8px 20px;">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if ($show_notification && $notif_text !== ""): ?>
        <div class="system-notification <?php echo $notif_class; ?>">
            <div class="notif-content"><?php echo $notif_text; ?></div>
        </div>
    <?php endif; ?>

