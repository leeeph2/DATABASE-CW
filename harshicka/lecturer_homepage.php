<?php
session_start();

// Security check: Kick them out if they are NOT logged in OR NOT a lecturer
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f0f2f5;
            padding: 50px;
            text-align: center;
        }
        .dashboard-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 0 auto;
        }
        .btn-logout {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="dashboard-card">
        <h1>Welcome, Lecturer <?php echo htmlspecialchars($_SESSION['username']); ?>! 🎓</h1>
        <p>Your secure session is active. This is your dashboard to manage internship results.</p>
        
        <a href="logout.php" class="btn-logout">Secure Log Out</a>
    </div>
</body>
</html>