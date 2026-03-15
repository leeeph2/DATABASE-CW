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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication | Internship System</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ========================================================
           BABY BLUE & WHITE GLASSMORPHISM - OTP SCREEN
           ======================================================== */
        
        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            color: #1e293b; 
            
            /* Animated Soft Baby Blue & White Gradient Background */
            background: linear-gradient(-45deg, #ffffff, #e0f2fe, #f0f9ff, #bae6fd);
            background-size: 400% 400%;
            animation: smoothGradient 15s ease infinite;
            min-height: 100vh;
            
            /* Center the card on the screen */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        @keyframes smoothGradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* --- Glassmorphism Card --- */
        .otp-card {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 8px 32px 0 rgba(14, 165, 233, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .otp-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 45px 0 rgba(14, 165, 233, 0.15);
            background: rgba(255, 255, 255, 0.65);
        }

        .otp-card h2 {
            margin-top: 0;
            margin-bottom: 10px;
            font-weight: 700;
            color: #0f172a;
        }

        .otp-card p {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        /* --- OTP Input Field --- */
        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .otp-input {
            width: 100%;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            font-size: 24px; /* Larger font for OTP digits */
            letter-spacing: 4px; /* Spaces out the numbers */
            text-align: center;
            color: #1e293b;
            font-weight: 600;
            outline: none;
            transition: all 0.3s ease;
            box-sizing: border-box;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .otp-input:focus {
            background: rgba(255, 255, 255, 0.9);
            border-color: #7dd3fc;
            box-shadow: 0 0 0 4px rgba(125, 211, 252, 0.3);
        }

        /* --- Submit Button --- */
        .btn-submit {
            width: 100%;
            margin-top: 10px;
            padding: 15px;
            /* Here is your new purple gradient! */
            background: linear-gradient(135deg, #96a3da 0%, #bd94e6 100%); 
            color: #ffffff;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            /* Updated shadow to match the purple */
            box-shadow: 0 10px 20px rgba(189, 148, 230, 0.2); 
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            /* Purple glowing shadow on hover */
            box-shadow: 0 15px 30px rgba(189, 148, 230, 0.4); 
            filter: brightness(1.05);
        }

        /* --- Error Message & Links --- */
        .error-message {
            color: #ef4444; /* Soft red */
            background: rgba(239, 68, 68, 0.1);
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .links {
            margin-top: 25px;
            font-size: 13px;
            color: #64748b;
        }

        .links a {
            color: #0ea5e9;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .links a:hover {
            color: #0284c7;
            text-decoration: underline;
        }
        
        /* Icon styling placeholder */
        .shield-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <div class="otp-card">
        <div class="shield-icon">🛡️</div>
        <h2>Verification Required</h2>
        <p>For your security, please enter the 6-digit authentication code to continue.</p>

        <?php if ($error_msg != ""): ?>
            <div class="error-message">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form action="verify_otp.php" method="POST">
            <div class="form-group">
                <label for="otp">Authentication Code</label>
                <input type="text" id="otp" name="otp" class="otp-input" maxlength="6" placeholder="• • • • • •" required autocomplete="off" autofocus>
            </div>

            <button type="submit" name="verify_otp" class="btn-submit">Verify Code</button>
        </form>

        <div class="links">
            Didn't receive a code? <a href="#">Resend Code</a><br><br>
            <a href="index.php">Return to Login</a>
        </div>
    </div>

</body>
</html>