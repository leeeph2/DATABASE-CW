<?php
session_start();
require("database.php");
date_default_timezone_set('Asia/Kuala_Lumpur'); // Or 'Europe/London'

/**
 * ROBUSTNESS CHECK: 
 * We use mysqli_real_escape_string and trim() to ensure no SQL injection 
 * or accidental spaces at the start/end of the username.
 */

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $password = $_POST['password'];

    // 1. Fetch user by username using Prepared Statements
    $sql = "SELECT user_id, username, password, role FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        
        // 2. Verify Hashed Password (Works with the hash we set in DB)
        if (password_verify($password, $user['password'])) {
            
            // 3. Generate 6-digit OTP for the Unique Factor requirement
            $otp = rand(100000, 999999);
            $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));

            // 4. Update the DB with the OTP and Expiry
            $update_sql = "UPDATE users SET otp_code = ?, otp_expires = ? WHERE user_id = ?";
            $upd_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($upd_stmt, "sss", $otp, $expires, $user['user_id']);
            
            if (mysqli_stmt_execute($upd_stmt)) {
                // 5. Success: Set temporary session (user is not fully logged in yet)
                $_SESSION['temp_user_id'] = $user['user_id'];
                
                // Redirect to the 2FA verification page
                header("Location: verify_otp.php");
                exit();
            } else {
                // Database error handled gracefully
                header("Location: index.php?error=db_error");
                exit();
            }

        } else {
            // Invalid Password
            header("Location: index.php?error=invalid_auth");
            exit();
        }
    } else {
        // User not found
        header("Location: index.php?error=invalid_auth");
        exit();
    }
} else {
    // If accessed directly without POSTing, send back to login
    header("Location: index.php");
    exit();
}
?>