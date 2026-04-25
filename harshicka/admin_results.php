<?php
session_start();
require("database.php");

// Search logic for Robustness
$search = $_GET['search'] ?? '';
$query = "SELECT s.student_id, s.student_name, a.total_mark, i.internship_status 
          FROM students s
          JOIN internships i ON s.student_id = i.student_id
          LEFT JOIN assessments a ON i.internship_id = a.internship_id
          WHERE s.student_id LIKE ? OR s.student_name LIKE ?";

$stmt = mysqli_prepare($conn, $query);
$search_param = "%$search%";
mysqli_stmt_bind_param($stmt, "ss", $search_param, $search_param);
mysqli_stmt_execute($stmt);
$results = mysqli_stmt_get_result($stmt);
?>