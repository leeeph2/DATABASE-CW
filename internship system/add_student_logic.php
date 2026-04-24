<?php
session_start();
require("database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] === 'Admin') {
    $sid = mysqli_real_escape_string($conn, $_POST['student_id']);
    $sname = mysqli_real_escape_string($conn, $_POST['student_name']);
    $pid = $_POST['programme_id'];
    $lid = $_POST['supervisor_id'];
    $company = mysqli_real_escape_string($conn, $_POST['company_name']);

    // Generate hashed password for the student's new account
    $hashed_pass = password_hash("student123", PASSWORD_DEFAULT);

    mysqli_begin_transaction($conn);

    try {
        // 1. Create User account
        $sql1 = "INSERT INTO users (user_id, username, password, role) VALUES (?, ?, ?, 'Student')";
        $stmt1 = mysqli_prepare($conn, $sql1);
        mysqli_stmt_bind_param($stmt1, "sss", $sid, $sid, $hashed_pass);
        mysqli_stmt_execute($stmt1);

        // 2. Create Student profile
        $sql2 = "INSERT INTO students (student_id, student_name, programme_id, supervisor_id) VALUES (?, ?, ?, ?)";
        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, "ssss", $sid, $sname, $pid, $lid);
        mysqli_stmt_execute($stmt2);

        mysqli_commit($conn);
        header("Location: admin_dashboard.php?msg=added");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: add_student.php?error=fail");
    }
}