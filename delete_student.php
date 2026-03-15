<?php
session_start();
require("database.php");

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $student_id = mysqli_real_escape_string($conn, $_GET['id']);

    // --- STEP 1: Fetch the name BEFORE deleting ---
    $name_query = mysqli_query($conn, "SELECT student_name FROM students WHERE student_id = '$student_id'");
    $student_data = mysqli_fetch_assoc($name_query);
    $deleted_name = $student_data['student_name'] ?? "Student";

    // --- STEP 2: Delete the records ---
    mysqli_query($conn, "DELETE FROM internships WHERE student_id = '$student_id'");
    mysqli_query($conn, "DELETE FROM students WHERE student_id = '$student_id'");
    mysqli_query($conn, "DELETE FROM users WHERE user_id = '$student_id' AND role = 'Student'");

    // --- STEP 3: Send the admin back with the encoded name ---
    $encoded_name = urlencode($deleted_name);
    header("Location: admin_dashboard.php?msg=deleted&name=$encoded_name");
    exit();
}

header("Location: admin_dashboard.php");
exit();
?>