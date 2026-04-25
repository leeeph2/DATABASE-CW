<?php
session_start();
require("database.php");

// 1. Security Check
if (!isset($_SESSION['user_id']) || trim($_SESSION['role']) !== 'Supervisor') {
    header("Location: index.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Capture Data
    $internship_id = mysqli_real_escape_string($conn, $_POST['internship_id']);
    $comments = mysqli_real_escape_string($conn, $_POST['comments']);
    
    // Capture the 8 fields (q1 to q8)
    $q1 = floatval($_POST['q1']);
    $q2 = floatval($_POST['q2']);
    $q3 = floatval($_POST['q3']);
    $q4 = floatval($_POST['q4']);
    $q5 = floatval($_POST['q5']);
    $q6 = floatval($_POST['q6']);
    $q7 = floatval($_POST['q7']);
    $q8 = floatval($_POST['q8']);

    // 3. Calculate Total Mark (Max 100)
    $total_mark = $q1 + $q2 + $q3 + $q4 + $q5 + $q6 + $q7 + $q8;

    // 4. Check if an Employer assessment already exists for this internship
    $check_sql = "SELECT assessment_id FROM assessments 
                  WHERE internship_id = '$internship_id' 
                  AND assessment_type = 'Employer'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        // UPDATE existing record
        $sql = "UPDATE assessments SET 
                q1 = '$q1', q2 = '$q2', q3 = '$q3', q4 = '$q4', 
                q5 = '$q5', q6 = '$q6', q7 = '$q7', q8 = '$q8', 
                total_mark = '$total_mark', 
                comments = '$comments',
                date_assessed = NOW()
                WHERE internship_id = '$internship_id' AND assessment_type = 'Employer'";
    } else {
        // INSERT new record
        $sql = "INSERT INTO assessments 
                (internship_id, assessment_type, q1, q2, q3, q4, q5, q6, q7, q8, total_mark, comments, date_assessed) 
                VALUES 
                ('$internship_id', 'Employer', '$q1', '$q2', '$q3', '$q4', '$q5', '$q6', '$q7', '$q8', '$total_mark', '$comments', NOW())";
    }

    if (mysqli_query($conn, $sql)) {
        // Redirect back to list with success message
        header("Location: evaluate_list_supervisor.php?msg=success");
    } else {
        // Error handling
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: evaluate_list_supervisor.php");
}
?>