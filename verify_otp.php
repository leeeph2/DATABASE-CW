<?php
session_start();
require("database.php");
date_default_timezone_set('Asia/Kuala_Lumpur'); //

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
        $error = "Invalid or expired code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication | Internship System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body class="auth-body">
    <div class="otp-card">
        <div class="shield-icon">🛡️</div>
        <h2>Security Check</h2>
        <p>Please enter the 6-digit authentication code sent to your database to complete your login.</p>
        
       <form method="POST">
    <input type="text" name="otp" class="otp-input" placeholder="••••••" maxlength="6" required autofocus autocomplete="off">
    <button type="submit">Complete Secure Login</button>
</form>

        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        
        <a href="index.php" class="back-link">← Back to Login</a>
    </div>
</body>
</html>