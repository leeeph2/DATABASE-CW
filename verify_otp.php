<?php
session_start();
require("database.php");
date_default_timezone_set('Asia/Kuala_Lumpur');

// 1. Security Check: Ensure user passed the initial login screen
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

        // Validate OTP and Expiry
        if ($entered_otp !== $user['otp_code']) {
            $error_msg = "Invalid authentication code.";
        } 
        elseif ($current_time > $user['otp_expires']) {
            $error_msg = "Your code has expired. Please log in again.";
        } 
        else {
            // SUCCESS: Elevate to full session
            $_SESSION['user_id'] = $user_id; 
            $_SESSION['username'] = $user['username'];
            
            // Format role for consistent check (Lecturer, Supervisor, Admin)
            $user_role = ucfirst(strtolower(trim($user['role']))); 
            $_SESSION['role'] = $user_role; 
            
            unset($_SESSION['temp_user_id']); 

            // Clear the used OTP for security
            $clear_stmt = mysqli_prepare($conn, "UPDATE users SET otp_code = NULL, otp_expires = NULL WHERE user_id = ?");
            mysqli_stmt_bind_param($clear_stmt, "s", $user_id);
            mysqli_stmt_execute($clear_stmt);

            // 2. REDIRECTION LOGIC: Updated for split dashboards
            if ($user_role === 'Admin') {
                header("Location: admin_dashboard.php");
                exit(); 
            } elseif ($user_role === 'Lecturer') {
                header("Location: lecturer_dashboard.php");
                exit();
            } elseif ($user_role === 'Supervisor') {
                header("Location: supervisor_dashboard.php");
                exit(); // IMPORTANT: Ensure this is here
            }
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
    <style>
        /* Specific styling for the OTP input to make it look professional */
        .otp-input {
            letter-spacing: 12px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            padding: 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            width: 100%;
            transition: border-color 0.3s;
        }
        .otp-input:focus {
            border-color: var(--primary-blue);
            outline: none;
        }
        .error-box {
            background: #fef2f2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid #fee2e2;
            text-align: center;
        }
    </style>
</head>
<body class="auth-body">

    <div class="login-card">
        <div class="login-header">
            <span class="stat-label" style="color: var(--primary-blue);">Security Protocol</span>
            <h1>Verification</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Enter the 6-digit code sent to your credentials.</p>
        </div>
        
        <?php if ($error_msg != ""): ?>
            <div class="error-box"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="verify_otp.php" method="POST">
            <div class="form-group">
                <label for="otp">Authentication Code</label>
                <input type="text" id="otp" name="otp" class="otp-input" 
                       maxlength="6" required autocomplete="off" 
                       autofocus placeholder="******" pattern="\d{6}">
            </div>
            
            <button type="submit" name="verify_otp" class="btn-primary" style="width: 100%; cursor: pointer; margin-top: 10px;">
                Verify & Access System
            </button>
        </form>
           
        <div style="text-align: center; margin-top: 24px;">
            <a href="index.php" class="back-link">← Back to Login</a>
        </div>
        
    </div>

</body>
</html>