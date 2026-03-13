<?php
// We include database.php to ensure the connection is active
// but we don't need to echo "Connected" here anymore.
require("database.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Login | Internship System</title>
    <style>
        /* Modern Minimalist Aesthetic */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            margin-bottom: 25px;
            color: #333;
            text-align: center;
            font-weight: 600;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box; /* Ensures padding doesn't affect width */
        }

        .password-container {
            position: relative;
        }

        /* Show/Hide Button Style */
        .toggle-btn {
            position: absolute;
            right: 10px;
            top: 10px;
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
        }

        button[name="login"] {
            width: 100%;
            padding: 12px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button[name="login"]:hover {
            background-color: #555;
        }

        .footer-links {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
        }

        .footer-links a {
            color: #888;
            text-decoration: none;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>System Login</h2>
        
        <form action="login_logic.php" method="POST">
            
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>

            <label for="password">Password</label>
            <div class="password-container">
                <input type="password" id="passwordField" name="password" placeholder="Enter your password" required>
                <button type="button" class="toggle-btn" onclick="togglePassword()">Show</button>
            </div>

            <button type="submit" name="login">Login</button>
        </form>

        <div class="footer-links">
            <a href="forget_password.php">Forgot Password? Click here</a>
        </div>
    </div>

    <script>
        // Function to hide/unhide password
        function togglePassword() {
            const passwordField = document.getElementById("passwordField");
            const toggleBtn = document.querySelector(".toggle-btn");
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleBtn.textContent = "Hide";
            } else {
                passwordField.type = "password";
                toggleBtn.textContent = "Show";
            }
        }
    </script>

</body>
</html>