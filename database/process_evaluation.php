<?php
session_start();
require("database.php");

// 1. Security Check: Only logged-in Assessors should process marks [cite: 13, 23]
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assessor') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Retrieve the 8 Assessment Aspect Scores [cite: 35-42]
    $int_id = $_POST['internship_id'];
    $s1 = (int)$_POST['score_tasks'];       // 10%
    $s2 = (int)$_POST['score_safety'];      // 10%
    $s3 = (int)$_POST['score_theory'];      // 10%
    $s4 = (int)$_POST['score_presentation'];// 15%
    $s5 = (int)$_POST['score_clarity'];     // 10%
    $s6 = (int)$_POST['score_learning'];    // 15%
    $s7 = (int)$_POST['score_project_mgmt'];// 15%
    $s8 = (int)$_POST['score_time_mgmt'];   // 15%
    $comments = mysqli_real_escape_string($conn, $_POST['comments']);

    // 3. Official Weightage Calculation [cite: 33, 35-43]
    // The system must minimize calculation errors by standardizing this process[cite: 33].
    $total_mark = ($s1 * 0.10) + ($s2 * 0.10) + ($s3 * 0.10) + 
                  ($s4 * 0.15) + ($s5 * 0.10) + ($s6 * 0.15) + 
                  ($s7 * 0.15) + ($s8 * 0.15);

    // 4. Generate Standardized Assessment ID (ASM-XXX) [cite: 54]
    $number_only = str_replace("INT-", "", $int_id);
    $asm_id = "ASM-" . $number_only;

    // 5. Secure Database Update using Prepared Statements 
    $sql = "INSERT INTO assessments (
                assessment_id, internship_id, score_tasks, score_safety, 
                score_theory, score_presentation, score_clarity, score_learning, 
                score_project_mgmt, score_time_mgmt, total_mark, comments
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                score_tasks=VALUES(score_tasks), 
                score_safety=VALUES(score_safety), 
                score_theory=VALUES(score_theory), 
                score_presentation=VALUES(score_presentation), 
                score_clarity=VALUES(score_clarity), 
                score_learning=VALUES(score_learning), 
                score_project_mgmt=VALUES(score_project_mgmt), 
                score_time_mgmt=VALUES(score_time_mgmt), 
                total_mark=VALUES(total_mark), 
                comments=VALUES(comments)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssiiiiiiiids", 
        $asm_id, $int_id, $s1, $s2, $s3, $s4, $s5, $s6, $s7, $s8, $total_mark, $comments
    );

    if (mysqli_stmt_execute($stmt)) {
        // 6. Automatically update Internship Status to 'Evaluated' [cite: 33]
        $update_status = "UPDATE internships SET internship_status='Evaluated' WHERE internship_id=?";
        $stmt_status = mysqli_prepare($conn, $update_status);
        mysqli_stmt_bind_param($stmt_status, "s", $int_id);
        mysqli_stmt_execute($stmt_status);
        
        header("Location: assessor_dashboard.php?msg=evaluated");
    } else {
        echo "Error in evaluation: " . mysqli_error($conn);
    }
}
?>