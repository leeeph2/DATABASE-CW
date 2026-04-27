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
    <script src="script.js" defer></script>
    
    <style>
        /* Modern Header UI/UX Enhancements */
        .nav-right-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .nav-link-item {
            text-decoration: none;
            color: #475569;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        .nav-link-item:hover { color: #1a5eb8; }

        .nav-divider {
            width: 1px;
            height: 24px;
            background: #cbd5e1;
        }

        /* Profile Badge Styling */
        .profile-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            padding: 4px 14px 4px 4px;
            border-radius: 99px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }
        .profile-badge:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .avatar-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        /* Dynamic Role Colors */
        .role-lecturer .avatar-circle { background: linear-gradient(135deg, #2563eb, #1e40af); }
        .role-supervisor .avatar-circle { background: linear-gradient(135deg, #059669, #047857); }
        .role-admin .avatar-circle { background: linear-gradient(135deg, #111827, #374151); /* Dark grey/black for Admin */ }
        .role-default .avatar-circle { background: #64748b; }

        .role-text {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        /* Icon Buttons */
        .icon-action {
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .icon-action:hover {
            color: #1a5eb8;
            background: #f1f5f9;
        }

        .btn-signout {
            background: #fee2e2;
            color: #b91c1c;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-signout:hover {
            background: #fca5a5;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <nav class="navbar-global">
        <div class="nav-container">
            <div class="nav-logo">
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

            <div class="nav-right-actions">
                <?php if (isset($_SESSION['user_id'])): 
                    // Automatically format the role to start with a capital letter (e.g. 'admin' becomes 'Admin')
                    $user_role = ucfirst(strtolower(trim($_SESSION['role'] ?? 'User')));
                    
                    $dashboard_link = match($user_role) {
    'Lecturer'   => 'lecturer_dashboard.php',
    'Supervisor' => 'supervisor_dashboard.php',
    'Admin'      => 'admin_dashboard.php',
    default      => 'index.php',
};

                    // Determine Profile Theme based on Role
                    $role_class = match($user_role) {
                        'Lecturer'   => 'role-lecturer',
                        'Supervisor' => 'role-supervisor',
                        'Admin'      => 'role-admin',
                        default      => 'role-default'
                    };
                ?>
                    
                    <a href="<?php echo $dashboard_link; ?>" class="nav-link-item">Dashboard</a>
                    
                    <div class="nav-divider"></div>

                    <div class="profile-badge <?php echo $role_class; ?>" title="Logged in as <?php echo htmlspecialchars($user_role); ?>">
                        <div class="avatar-circle">
                            <?php if ($user_role === 'Lecturer'): ?>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                            
                            <?php elseif ($user_role === 'Supervisor'): ?>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                            
                            <?php elseif ($user_role === 'Admin'): ?>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            
                            <?php else: ?>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <?php endif; ?>
                        </div>
                        <span class="role-text"><?php echo htmlspecialchars($user_role); ?></span>
                    </div>

                    <?php if (in_array($user_role, ['Lecturer', 'Supervisor'])): ?>
                        <a href="setting.php" title="Account Settings" class="icon-action">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                        </a>
                    <?php endif; ?>

                    <div class="nav-divider"></div>

                    <a href="logout.php" class="btn-signout">Sign Out</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">