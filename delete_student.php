<?php
session_start();
require("database.php");

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

if (isset($_GET['id'])) {
    $student_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // 1. Fetch the student's name BEFORE deleting them
    $name_query = mysqli_query($conn, "SELECT student_name FROM students WHERE student_id = '$student_id'");
    $student_data = mysqli_fetch_assoc($name_query);
    $deleted_name = $student_data['student_name'] ?? 'Student';

    // 2. Perform the deletion
    $delete_query = "DELETE FROM students WHERE student_id = '$student_id'";
    
    if (mysqli_query($conn, $delete_query)) {
        // 3. URL-encode the name and pass it to the header
        $encoded_name = urlencode($deleted_name);
        header("Location: view_students.php?msg=deleted&name=$encoded_name");
        exit();
    } else {
        // If deletion fails
        header("Location: view_students.php?error=db_error");
        exit();
    }
} else {
    header("Location: view_students.php");
    exit();
}
?>