<?php
session_start(); // Find the active session

// Remove all session variables (clears the user_id, role, etc.)
session_unset(); 

// Destroy the session completely for security
session_destroy(); 

// Redirect back to your actual login page (index.php)
header("Location: index.php");
exit();
?>