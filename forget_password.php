<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Internship System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body class="auth-body">
    <div class="otp-card">
        <div style="font-size: 40px; margin-bottom: 20px;">🔑</div>
        <h2 style="font-weight: 800; color: #0f172a; margin-bottom: 10px;">Forgot Password?</h2>
        <p style="color: #64748b; font-size: 14px; margin-bottom: 25px; line-height: 1.5;">
            Enter your username below. Your department admin will reset your password to the default <strong>(pass123)</strong> within 24 hours.
        </p>
        
        <form action="reset_logic.php" method="POST">
    <input type="text" name="username" class="username-input" placeholder="University Username" required>
    <button type="submit" name="reset_request">Request Reset</button>
</form>

        <a href="index.php" class="dash-back-link" style="margin-top: 25px; display: inline-block; font-size: 14px;">← Back to Login</a>
    </div>
</body>

</html>