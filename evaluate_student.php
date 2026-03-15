<?php
session_start();
require("database.php");

// 1. SECURITY: Only Assessors can evaluate
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assessor') {
    header("Location: index.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];
$message = "";

if (!isset($_GET['id'])) {
    header("Location: assessor_dashboard.php");
    exit();
}
$student_id = mysqli_real_escape_string($conn, $_GET['id']);

// 2. FETCH STUDENT & INTERNSHIP DATA
// We must ensure this student actually belongs to this logged-in Assessor
$query = "SELECT s.student_name, i.internship_id, a.total_mark, a.comments 
          FROM students s 
          LEFT JOIN internships i ON s.student_id = i.student_id 
          LEFT JOIN assessments a ON i.internship_id = a.internship_id 
          WHERE s.student_id = '$student_id' AND s.supervisor_id = '$lecturer_id'";

$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) == 0) {
    // Security kick: Student doesn't belong to them or doesn't exist
    header("Location: assessor_dashboard.php");
    exit();
}
$student_data = mysqli_fetch_assoc($result);
$internship_id = $student_data['internship_id'];
$student_name = $student_data['student_name'];

// 3. HANDLE FORM SUBMISSION
if (isset($_POST['submit_evaluation'])) {
    if (!$internship_id) {
        // Upgraded to global floating-alert
        $message = "<div class='floating-alert error'>Error: This student does not have an active internship record to evaluate.</div>";
    } else {
        // Collect exact marks (The HTML form ensures they don't exceed the weightage)
        $m1 = (float)$_POST['mark_tasks'];    // Max 10
        $m2 = (float)$_POST['mark_safety'];   // Max 10
        $m3 = (float)$_POST['mark_theory'];   // Max 10
        $m4 = (float)$_POST['mark_report'];   // Max 15
        $m5 = (float)$_POST['mark_clarity'];  // Max 10
        $m6 = (float)$_POST['mark_learning']; // Max 15
        $m7 = (float)$_POST['mark_project'];  // Max 15
        $m8 = (float)$_POST['mark_time'];     // Max 15
        
        $comments = mysqli_real_escape_string($conn, $_POST['comments']);
        
        // Final automated calculation (System Enforcement)
        $total_mark = $m1 + $m2 + $m3 + $m4 + $m5 + $m6 + $m7 + $m8;

        // Check if assessment already exists
        $check_ass = mysqli_query($conn, "SELECT * FROM assessments WHERE internship_id = '$internship_id'");
        
        if (mysqli_num_rows($check_ass) > 0) {
            // Update existing marks
            $sql = "UPDATE assessments SET total_mark = '$total_mark', comments = '$comments' WHERE internship_id = '$internship_id'";
        } else {
            // Generate a unique Assessment ID (e.g., ASM-4829)
            $new_ass_id = "ASM-" . rand(1000, 9999); 
            
            // Insert new marks WITH the generated ID
            $sql = "INSERT INTO assessments (assessment_id, internship_id, total_mark, comments) 
                    VALUES ('$new_ass_id', '$internship_id', '$total_mark', '$comments')";
        }

        if (mysqli_query($conn, $sql)) {
            // Upgraded to global floating-alert
            $message = "<div class='floating-alert success'>Evaluation saved! Final Score: <strong>$total_mark%</strong></div>";
            // Update current variables to reflect changes on screen
            $student_data['total_mark'] = $total_mark;
            $student_data['comments'] = $comments;
        } else {
            // Upgraded to global floating-alert
            $message = "<div class='floating-alert error'>Database Error: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Evaluate Student | Internship System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="evaluate-page">

<div class="container">
    <a href="assessor_dashboard.php" class="dash-back-link">← Back to My Students</a>
    
    <?php if ($message != "") echo $message; ?>

    <div class="glass-card">
        <h2>Internship Assessment</h2>
        <div class="subtitle">Student: <strong><?php echo htmlspecialchars($student_name); ?></strong> (<?php echo htmlspecialchars($student_id); ?>)</div>

        <form method="POST">
            <div class="eval-grid">
                
                <div class="eval-row">
                    <div><span class="eval-desc">Undertaking Tasks / Projects</span> <span class="eval-weight">10%</span></div>
                    <div class="eval-input-group">
                        <input type="number" name="mark_tasks" class="mark-input" step="0.5" min="0" max="10" required> <span style="font-weight:700; color:#94a3b8;">/ 10</span>
                    </div>
                </div>

                <div class="eval-row">
                    <div><span class="eval-desc">Health and Safety Requirements</span> <span class="eval-weight">10%</span></div>
                    <div class="eval-input-group">
                        <input type="number" name="mark_safety" class="mark-input" step="0.5" min="0" max="10" required> <span style="font-weight:700; color:#94a3b8;">/ 10</span>
                    </div>
                </div>

                <div class="eval-row">
                    <div><span class="eval-desc">Connectivity & Use of Theory</span> <span class="eval-weight">10%</span></div>
                    <div class="eval-input-group">
                        <input type="number" name="mark_theory" class="mark-input" step="0.5" min="0" max="10" required> <span style="font-weight:700; color:#94a3b8;">/ 10</span>
                    </div>
                </div>

                <div class="eval-row">
                    <div><span class="eval-desc">Presentation of Written Report</span> <span class="eval-weight">15%</span></div>
                    <div class="eval-input-group">
                        <input type="number" name="mark_report" class="mark-input" step="0.5" min="0" max="15" required> <span style="font-weight:700; color:#94a3b8;">/ 15</span>
                    </div>
                </div>

                <div class="eval-row">
                    <div><span class="eval-desc">Clarity of Language & Illustration</span> <span class="eval-weight">10%</span></div>
                    <div class="eval-input-group">
                        <input type="number" name="mark_clarity" class="mark-input" step="0.5" min="0" max="10" required> <span style="font-weight:700; color:#94a3b8;">/ 10</span>
                    </div>
                </div>

                <div class="eval-row">
                    <div><span class="eval-desc">Lifelong Learning Activities</span> <span class="eval-weight">15%</span></div>
                    <div class="eval-input-group">
                        <input type="number" name="mark_learning" class="mark-input" step="0.5" min="0" max="15" required> <span style="font-weight:700; color:#94a3b8;">/ 15</span>
                    </div>
                </div>

                <div class="eval-row">
                    <div><span class="eval-desc">Project Management</span> <span class="eval-weight">15%</span></div>
                    <div class="eval-input-group">
                        <input type="number" name="mark_project" class="mark-input" step="0.5" min="0" max="15" required> <span style="font-weight:700; color:#94a3b8;">/ 15</span>
                    </div>
                </div>

                <div class="eval-row">
                    <div><span class="eval-desc">Time Management</span> <span class="eval-weight">15%</span></div>
                    <div class="eval-input-group">
                        <input type="number" name="mark_time" class="mark-input" step="0.5" min="0" max="15" required> <span style="font-weight:700; color:#94a3b8;">/ 15</span>
                    </div>
                </div>
            </div>

            <h3 style="color: #0f172a; margin-bottom: 15px;">Qualitative Justification</h3>
            <textarea name="comments" placeholder="Provide comments to justify the scores given above. This feedback is critical for standardizing the assessment process..." required><?php echo htmlspecialchars($student_data['comments'] ?? ''); ?></textarea>

            <div class="total-display">
                <div class="total-text">Final Internship Score<br><small style="font-weight:400; opacity:0.8;">Automatically calculated based on faculty weightages</small></div>
                <div class="total-score"><span id="liveTotal">0</span><span style="font-size: 24px; opacity:0.8;">%</span></div>
            </div>

            <button type="submit" name="submit_evaluation" class="btn-save" style="margin-top: 25px;">🔒 Save & Lock Official Assessment</button>
        </form>
    </div>
</div>

<script>
    const inputs = document.querySelectorAll('.mark-input');
    const totalDisplay = document.getElementById('liveTotal');

    function calculateTotal() {
        let total = 0;
        inputs.forEach(input => {
            // Prevent user from typing over the max weightage
            if(parseFloat(input.value) > parseFloat(input.max)) {
                input.value = input.max;
            }
            if(parseFloat(input.value) < 0) {
                input.value = 0;
            }
            
            total += parseFloat(input.value) || 0;
        });
        totalDisplay.innerText = total.toFixed(1); // Keep it to 1 decimal place
    }

    // Attach event listeners to all inputs
    inputs.forEach(input => {
        input.addEventListener('input', calculateTotal);
    });
</script>

</body>
</html>