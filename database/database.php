<?php
// 1. Force PHP to show errors (Essential for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Database Connection Credentials
$db_server = "localhost";
$db_user   = "root";
$db_pass   = "root"; // MAMP default is 'root'. If using XAMPP, change to ""
$db_name   = "internship_system";

// 3. Initialize connection variable
$conn = null;

// 4. Attempt to connect using a Try-Catch block
try {
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
    
    // Check if the connection actually worked
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }

} catch (Exception $e) {
    // This will stop the page and show you the error if the DB is down
    die("Database Error: " . $e->getMessage());
}

// 5. Optional: Check connection status (Keep commented out for production)
// if($conn) { echo "Connected successfully"; }
?>