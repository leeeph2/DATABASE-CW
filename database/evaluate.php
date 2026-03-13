<?php
session_start();
require("database.php");

// 1. Security Check: Only Assessors allowed [cite: 13, 23]
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assessor') {
    header("Location: index.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];
$student_id = $_GET['student_id'] ?? '';

// 2. Authorization Check: Is this student assigned to THIS lecturer? 
$check_sql = "SELECT s.student_name, i.internship_id, i.company_name 
              FROM students s 
              JOIN internships i ON s.student_id = i.student_id 
              WHERE s.student_id = ? AND s.supervisor_id = ?";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, "ss", $student_id, $lecturer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("<div style='color:red; padding:20px;'>Error: You are not authorized to evaluate this student or record not found.</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Evaluate Student - <?php echo $data['student_name']; ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; color: #333; padding: 40px; }
        .form-container { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 30px; }
        .group-box { border: 1px solid #eee; padding: 20px; border-radius: 6px; margin-bottom: 25px; }
        .group-header { font-weight: bold; color: #2c3e50; margin-bottom: 15px; display: block; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; }
        .input-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        label { font-size: 14px; flex: 1; }
        input[type="number"] { width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center; }
        textarea { width: 100%; height: 100px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; resize: none; margin-top: 10px; }
        .btn-submit { background: #333; color: white; padding: 12px 25px; border: none; border-radius: 4px; cursor: pointer; float: right; font-weight: bold; }
        .btn-submit:hover { background: #000; }
        .weight-tag { font-size: 11px; color: #888; margin-left: 5px; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Student Evaluation</h2>
    <p>Student: <strong><?php echo $data['student_name']; ?> (<?php echo $student_id; ?>)</strong></p>
    <p>Company: <strong><?php echo $data['company_name']; ?></strong></p>

    <form action="process_evaluation.php" method="POST">
        <input type="hidden" name="internship_id" value="<?php echo $data['internship_id']; ?>">

        <div class="group-box">
            <span class="group-header">Group 1: Technical (30%)</span>
            <div class="input-row">
                <label>Undertaking Tasks/Projects <span class="weight-tag">(10%)</span></label>
                <input type="number" name="score_tasks" min="0" max="100" required>
            </div>
            <div class="input-row">
                <label>Health and Safety at Workplace <span class="weight-tag">(10%)</span></label>
                <input type="number" name="score_safety" min="0" max="100" required>
            </div>
            <div class="input-row">
                <label>Connectivity & Theoretical Knowledge <span class="weight-tag">(10%)</span></label>
                <input type="number" name="score_theory" min="0" max="100" required>
            </div>
        </div>

        <div class="group-box">
            <span class="group-header">Group 2: Communication (25%)</span>
            <div class="input-row">
                <label>Written Report Presentation <span class="weight-tag">(15%)</span></label>
                <input type="number" name="score_presentation" min="0" max="100" required>
            </div>
            <div class="input-row">
                <label>Clarity of Language/Illustration <span class="weight-tag">(10%)</span></label>
                <input type="number" name="score_clarity" min="0" max="100" required>
            </div>
        </div>

        <div class="group-box">
            <span class="group-header">Group 3: Professionalism (45%)</span>
            <div class="input-row">
                <label>Lifelong Learning Activities <span class="weight-tag">(15%)</span></label>
                <input type="number" name="score_learning" min="0" max="100" required>
            </div>
            <div class="input-row">
                <label>Project Management <span class="weight-tag">(15%)</span></label>
                <input type="number" name="score_project_mgmt" min="0" max="100" required>
            </div>
            <div class="input-row">
                <label>Time Management <span class="weight-tag">(15%)</span></label>
                <input type="number" name="score_time_mgmt" min="0" max="100" required>
            </div>
        </div>

        <div class="group-box">
            <span class="group-header">Qualitative Feedback</span>
            [cite_start]<p style="font-size: 12px; color: #666;">Provide justification for the scores given[cite: 31].</p>
            <textarea name="comments" placeholder="Enter feedback here..." required></textarea>
        </div>

        <button type="submit" class="btn-submit">Submit Evaluation</button>
        <div style="clear: both;"></div>
    </form>
</div>

</body>
</html>