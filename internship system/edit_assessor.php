<?php
session_start();
require("database.php");

// 1. SECURITY: Only Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

$message = "";
$assessor_data = null;

// 2. FETCH EXISTING DATA
// We grab the ID from the URL (e.g., edit_assessor.php?id=LEC-001)
if (isset($_GET['id'])) {
    $target_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    $query = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$target_id'");
    if (mysqli_num_rows($query) > 0) {
        $assessor_data = mysqli_fetch_assoc($query);
    } else {
        die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>Assessor not found.</h2><a href='manage_assessors.php'>Go Back</a></div>");
    }
} else {
    // If someone tries to load this page without an ID, send them back
    header("Location: manage_assessors.php");
    exit();
}

// 3. UPDATE FUNCTIONALITY
if (isset($_POST['update_assessor'])) {
    $uid = mysqli_real_escape_string($conn, $_POST['user_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $new_password = $_POST['password'];

    // If the admin typed a new password, hash it and update it
    if (!empty($new_password)) {
        $hashed_pass = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET username='$username', full_name='$full_name', password='$hashed_pass' WHERE user_id='$uid'";
    } else {
        // If the password box is left blank, keep their old password
        $update_sql = "UPDATE users SET username='$username', full_name='$full_name' WHERE user_id='$uid'";
    }

    if (mysqli_query($conn, $update_sql)) {
        $message = "<div class='floating-alert success'>Assessor updated successfully!</div>";
        // Update our local array so the form shows the new typed values instantly
        $assessor_data['username'] = $username;
        $assessor_data['full_name'] = $full_name;
    } else {
        $message = "<div class='floating-alert error'>Error updating database.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Assessor | Internship System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="manage-assessors-page">

<div class="container">
    <a href="manage_assessors.php" class="dash-back-link">← Back to Manage Assessors</a>
    <br><br>

    <?php if ($message != "") echo $message; ?>

    <div class="glass-card">
        <h2>Edit Staff Record</h2>
        <p style="color: #64748b; margin-bottom: 20px;">Updating details for ID: <strong><?php echo htmlspecialchars($assessor_data['user_id']); ?></strong></p>
        
        <form method="POST" action="">
            <!-- Hidden field to safely pass the ID when the form submits -->
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($assessor_data['user_id']); ?>">
            
            <div class="form-grid">
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($assessor_data['username']); ?>" required>
                </div>
                
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($assessor_data['full_name']); ?>" required>
                </div>
                
                <div class="input-group">
                    <!-- If this is left blank, the PHP logic knows to leave the old password alone -->
                    <label>New Password <em>(Leave blank to keep current)</em></label>
                    <input type="password" name="password" placeholder="Type new password...">
                </div>
            </div>
            
            <button type="submit" name="update_assessor" class="btn-purple" style="margin-top: 20px; width: 100%; border-radius: 14px; padding: 15px;">Save Changes</button>
        </form>
    </div>
</div>

</body>
</html>