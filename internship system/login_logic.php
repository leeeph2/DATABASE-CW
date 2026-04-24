<?php
session_start();
require("database.php");
date_default_timezone_set('Asia/Kuala_Lumpur');

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT user_id, password, role FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        // Use password_verify to check hashed passwords
        if (password_verify($password, $user['password'])) {
            
            // Generate a 6-digit OTP
            $otp_code = rand(100000, 999999);
            $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));
            
            // Store OTP in database for verification
            $update_sql = "UPDATE users SET otp_code = ?, otp_expires = ? WHERE user_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "sss", $otp_code, $expires, $user['user_id']);
            mysqli_stmt_execute($update_stmt);

            // SET THE SECURITY TRIGGER: temp_user_id
            // This prevents jumping back to login from verify_otp.php
            $_SESSION['temp_user_id'] = $user['user_id'];

            // Redirect to the OTP screen
            header("Location: verify_otp.php");
            exit();
        } else {
            header("Location: index.php?error=invalid_credentials");
        }
    } else {
        header("Location: index.php?error=user_not_found");
    }
}
?>