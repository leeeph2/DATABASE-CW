<?php
session_start();
require("database.php");

// 1. Check if the form was actually submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. Grab the data from the form 
    // (Assuming your form inputs are named 'username' and 'new_password')
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $new_pass = $_POST['new_password'];

    // 3. Securely scramble (hash) the new password
    $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);

    // 4. Update the database
    $update_query = "UPDATE users SET password = '$hashed_pass' WHERE username = '$user'";
    
    if (mysqli_query($conn, $update_query)) {
        // If it works, send them back to the login page with a success message in the URL
        header("Location: index.php?msg=reset_success");
        exit();
    } else {
        // If the database fails, show an error
        die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>Database Error</h2><p>Could not reset password.</p><a href='index.php'>Go Back</a></div>");
    }

} else {
    // If someone tries to visit this URL directly without submitting a form, kick them back to login
    header("Location: index.php");
    exit();
}
?>