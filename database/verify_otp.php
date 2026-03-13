<?php
session_start();
require("database.php");
date_default_timezone_set('Asia/Kuala_Lumpur'); // Or 'Europe/London'

// If they haven't passed the first login, send them back
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_otp = mysqli_real_escape_string($conn, $_POST['otp']);
    $uid = $_SESSION['temp_user_id'];

    // Check if OTP matches AND has not expired (within 10 mins)
    $sql = "SELECT * FROM users WHERE user_id = ? AND otp_code = ? AND otp_expires > NOW()";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $uid, $input_otp);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        // SUCCESS: Clear OTP from database and set full session
        mysqli_query($conn, "UPDATE users SET otp_code = NULL, otp_expires = NULL WHERE user_id = '$uid'");
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        unset($_SESSION['temp_user_id']); // Remove temp ID

        // Redirect based on role
        if ($user['role'] == 'Admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: assessor_dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid or expired code. Please check phpMyAdmin.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verify Login | 2FA</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .otp-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 350px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 20px 0; border: 1px solid #ddd; border-radius: 6px; font-size: 20px; text-align: center; letter-spacing: 4px; }
        button { width: 100%; padding: 12px; background: #333; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .error { color: red; font-size: 14px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="otp-card">
        <h2>Security Check</h2>
        <p>Enter the 6-digit code from your database.</p>
        
        <form method="POST">
            <input type="text" name="otp" placeholder="000000" maxlength="6" required autofocus autocomplete="off">
            <button type="submit">Complete Login</button>
        </form>

        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        <br>
        <a href="index.php" style="font-size: 12px; color: #888;">Back to Login</a>
    </div>
</body>
</html>