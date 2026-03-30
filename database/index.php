<?php
// We include database.php to ensure the connection is active
require("database.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Login | Internship System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body class="auth-body">
    <div class="login-bubble-card">
        <h1>System Login</h1>
        <h3>Internship Result Management System</h3>

        <?php if(isset($_GET['error'])): ?>
            <div class="error-msg">
                <?php 
                    if($_GET['error'] == 'invalid_credentials') echo "Incorrect username or password.";
                    else if($_GET['error'] == 'user_not_found') echo "Account does not exist.";
                ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" action="login_logic.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="e.g. admin" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="passwordField">Password</label>
                <input type="password" id="passwordField" name="password" placeholder="*******" required autocomplete="current-password">
            </div>

            <button type="submit" name="login" class="btn-submit">Secure Login</button>
        </form>
        
        <div class="footer-links">
            <a href="forget_password.php">Forgot Password?</a>
        </div>
    </div>
</body>
</html>