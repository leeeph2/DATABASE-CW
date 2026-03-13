<?php
session_start();
require("database.php");

$sid = $_GET['id'];

$sql = "SELECT s.*, a.*, i.company_name 
        FROM students s
        JOIN internships i ON s.student_id = i.student_id
        LEFT JOIN assessments a ON i.internship_id = a.internship_id
        WHERE s.student_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $sid);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
?>

<h3>Assessment Breakdown</h3>
<ul>
    <li>Tasks (10%): <?php echo $data['score_tasks']; ?></li>
    <li>Safety (10%): <?php echo $data['score_safety']; ?></li>
    <li>Theory (10%): <?php echo $data['score_theory']; ?></li>
    </ul>
<strong>Final Mark: <?php echo $data['total_mark']; ?>%</strong>