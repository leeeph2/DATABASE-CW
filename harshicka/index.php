<?php
// Include the database connection
require("database.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Login | Internship System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <style>
        /* Container for positioning the toggle image */
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        /* Styling for the eye image */
        .toggle-icon {
            position: absolute;
            right: 12px;
            width: 18px;
            height: auto;
            cursor: pointer;
            user-select: none;
            opacity: 0.6;
            transition: opacity 0.2s;
        }

        .toggle-icon:hover {
            opacity: 1;
        }

        /* Ensure input padding doesn't let text overlap the icon */
        #passwordField {
            padding-right: 45px;
            width: 100%;
        }
    </style>
</head>

<body class="auth-body">
    <div class="login-card">
        <div class="login-header">
            <span class="stat-label">University of Nottingham Malaysia</span>
            <h1>System Login</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Internship Result Management System</p>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div style="display:flex; align-items:center; gap:10px; border-left:3px solid #2563eb; background:#eff6ff; border-radius:0 6px 6px 0; padding:10px 14px; margin-bottom:20px;">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;">
                    <circle cx="8" cy="8" r="7.25" stroke="#2563eb" stroke-width="1.5"/>
                    <line x1="8" y1="7" x2="8" y2="11" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round"/>
                    <circle cx="8" cy="5" r="0.75" fill="#2563eb"/>
                </svg>
                <p style="margin:0; font-size:13px; color:#1e40af; font-weight:500;">
                    <?php 
                        if($_GET['error'] == 'invalid_credentials') echo "Incorrect username or password.";
                        else if($_GET['error'] == 'user_not_found') echo "Account does not exist.";
                        else if($_GET['error'] == 'unauthorized') echo "Please log in to access the dashboard.";
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <form id="loginForm" action="login_logic.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="e.g. admin" required autocomplete="username">
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label for="passwordField">Password</label>
                <div class="password-container">
                    <input type="password" id="passwordField" name="password" placeholder="*******" required autocomplete="current-password">
                    <img src="close.png" id="togglePassword" class="toggle-icon" alt="Toggle Visibility">
                </div>
            </div>

            <button type="submit" name="login" class="btn-primary" style="width: 100%; cursor: pointer;">Secure Login</button>
        </form>
        
        <div style="margin-top: 25px; text-align: center;">
            <a href="forget_password.php" style="color: var(--primary-blue); text-decoration: none; font-size: 13px; font-weight: 600;">Forgot Password?</a>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordField = document.querySelector('#passwordField');

        togglePassword.addEventListener('click', function () {
            // Check current type and flip it
            const isPassword = passwordField.getAttribute('type') === 'password';
            passwordField.setAttribute('type', isPassword ? 'text' : 'password');
            
            // Swap image source based on new state
            // If it is now 'text' (visible), show open.png. Else show close.png.
            this.src = isPassword ? 'open.png' : 'close.png';
        });
    </script>
</body>
</html>