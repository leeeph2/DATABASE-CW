<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Internship System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body class="auth-body">
    <div class="login-card">

        <div class="login-header">
            <h1>Forgot Password?</h1>
            <p class="stat-label" style="text-transform: none; font-weight: 400; font-size: 14px; line-height: 1.6; margin-top: 8px; letter-spacing: 0;">
                Enter your username below. Your department admin will reset your password to the default <strong>(pass123)</strong> within 24 hours.
            </p>
        </div>

        <form action="reset_logic.php" method="POST">
            <div class="form-group">
                <label for="username">University Username</label>
                <input type="text" id="username" name="username" placeholder="e.g. 20715097" required>
            </div>
            <button type="submit" name="reset_request" class="btn-primary" style="width: 100%;">
                Request Reset
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px;">
            <a href="index.php" class="back-link">← Back to Login</a>
        </div>

    </div>
</body>

</html>