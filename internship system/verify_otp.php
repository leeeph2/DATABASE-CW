<?php
session_start();
require("database.php");
date_default_timezone_set('Asia/Kuala_Lumpur');

// Security Check: Ensure user passed the initial login screen
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: index.php");
    exit();
}

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_otp'])) {
    $entered_otp = trim($_POST['otp']);
    $user_id = $_SESSION['temp_user_id'];

    $sql = "SELECT username, role, otp_code, otp_expires FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        $current_time = date("Y-m-d H:i:s");

        if ($entered_otp !== $user['otp_code']) {
            $error_msg = "Invalid authentication code.";
        } 
        elseif ($current_time > $user['otp_expires']) {
            $error_msg = "Your code has expired. Please log in again.";
        } 
        else {
            // Success: Elevate to full session
            $_SESSION['user_id'] = $user_id; 
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = ucfirst(strtolower(trim($user['role']))); 
            
            unset($_SESSION['temp_user_id']); 

            // Clear the used OTP
            $clear_stmt = mysqli_prepare($conn, "UPDATE users SET otp_code = NULL, otp_expires = NULL WHERE user_id = ?");
            mysqli_stmt_bind_param($clear_stmt, "s", $user_id);
            mysqli_stmt_execute($clear_stmt);

            // Redirect based on role
            if ($_SESSION['role'] == 'Admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: assessor_dashboard.php");
            }
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Verification | Internship System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="auth-body">

    <div class="login-card">
        <div class="login-header">
            <span class="stat-label" style="color: var(--primary-blue);">Security Protocol</span>
            <h1>Verification</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Please enter your 6-digit authentication code.</p>
        </div>
        
        <?php if ($error_msg != ""): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="verify_otp.php" method="POST">
            <div class="form-group" style="text-align: center;">
                <label for="otp" style="text-align: left;">Authentication Code</label>
                <input type="text" id="otp" name="otp" class="otp-input" maxlength="6" required autocomplete="off" autofocus placeholder="••••••">
            </div>
            
            <button type="submit" name="verify_otp" class="btn-primary" style="width: 100%; cursor: pointer;">
                Verify & Access System
            </button>
        </form>

        <div style="margin-top: 30px; text-align: center;">
            <a href="index.php" style="color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 700; text-transform: uppercase;">Return to Login</a>
        </div>
    </div>

</body>
</html>