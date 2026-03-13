<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | Internship System</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 400px; text-align: center; }
        h2 { color: #333; margin-bottom: 10px; }
        p { color: #666; font-size: 14px; margin-bottom: 25px; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #333; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .back { margin-top: 20px; display: block; color: #007bff; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>

    <div class="card">
        <h2>Forgot Password?</h2>
        <p>Enter your username below. Your department admin will reset your password to the default <strong>(pass123)</strong> within 24 hours.</p>
        
        <form action="reset_logic.php" method="POST">
            <input type="text" name="username" placeholder="University Username" required>
            <button type="submit" name="reset_request">Request Reset</button>
        </form>

        <a href="index.php" class="back">← Back to Login</a>
    </div>

</body>
</html>