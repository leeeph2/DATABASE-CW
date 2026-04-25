<?php
// 1. Start Session & Database Connection
session_start();
require("database.php");

// 2. Security Check: Only allow Supervisors
if (!isset($_SESSION['user_id']) || trim($_SESSION['role']) !== 'Supervisor') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$supervisor_id = $_SESSION['user_id'];

// 3. Fetch Student & Internship Details
if (isset($_GET['id'])) {
    $internship_id_url = mysqli_real_escape_string($conn, $_GET['id']);
    
    $query = "SELECT s.student_name, s.student_id, i.internship_id, i.company_name 
              FROM students s 
              INNER JOIN internships i ON s.student_id = i.student_id 
              WHERE i.internship_id = '$internship_id_url' 
              AND i.supervisor_id = '$supervisor_id'";
              
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 0) {
        header("Location: evaluate_list_supervisor.php?error=unauthorized_access"); 
        exit();
    }
    
    $student = mysqli_fetch_assoc($result);
    $internship_id = $student['internship_id'];

    // Pre-fill logic: Check for existing Industry assessment based on your table schema
    $check_q = mysqli_query($conn, "SELECT * FROM assessments WHERE internship_id = '$internship_id' AND assessment_type = 'Industry' LIMIT 1");
    $existing = mysqli_fetch_assoc($check_q);
} else {
    header("Location: evaluate_list_supervisor.php");
    exit();
}

// 4. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mapping form data to your specific column names
    $s1 = floatval($_POST['score_tasks'] ?? 0);
    $s2 = floatval($_POST['score_safety'] ?? 0);
    $s3 = floatval($_POST['score_theory'] ?? 0);
    $s4 = floatval($_POST['score_presentation'] ?? 0);
    $s5 = floatval($_POST['score_clarity'] ?? 0);
    $s6 = floatval($_POST['score_learning'] ?? 0);
    $s7 = floatval($_POST['score_project_mgmt'] ?? 0);
    $s8 = floatval($_POST['score_time_mgmt'] ?? 0);

    $final_score = $s1 + $s2 + $s3 + $s4 + $s5 + $s6 + $s7 + $s8;
    $feedback = mysqli_real_escape_string($conn, $_POST['comments']);

    if (mysqli_num_rows($check_q) > 0) {
        // UPDATE existing record
        $sql = "UPDATE assessments SET 
                score_tasks='$s1', score_safety='$s2', score_theory='$s3', score_presentation='$s4', 
                score_clarity='$s5', score_learning='$s6', score_project_mgmt='$s7', score_time_mgmt='$s8', 
                total_mark = '$final_score', comments = '$feedback' 
                WHERE internship_id = '$internship_id' AND assessment_type = 'Industry'";
    } else {
        // INSERT new record using your specific primary key and enum types
        $ass_id = "IND-" . substr(time(), -10);
        $sql = "INSERT INTO assessments (assessment_id, internship_id, assessor_id, assessment_type, assessor_type, 
                score_tasks, score_safety, score_theory, score_presentation, score_clarity, score_learning, 
                score_project_mgmt, score_time_mgmt, total_mark, comments) 
                VALUES ('$ass_id', '$internship_id', '$supervisor_id', 'Industry', 'Supervisor', 
                '$s1', '$s2', '$s3', '$s4', '$s5', '$s6', '$s7', '$s8', '$final_score', '$feedback')";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: evaluate_list_supervisor.php?msg=evaluated");
        exit();
    } else {
        die("Database Error: " . mysqli_error($conn));
    }
}

include("header.php");
?>

<div id="eval-page" class="centered-wrapper">
    <div class="ep-header-row">
        <div class="ep-header-left">
            <a href="evaluate_list_supervisor.php" class="ep-breadcrumb">&larr; Back to Evaluation List</a>
            <h1 class="ep-page-title">Industry Evaluation</h1>
        </div>
        <div class="ep-header-right">
            <span class="ep-progress-label">Completion Progress</span>
            <span id="progress-text">0 / 8 Fields</span>
        </div>
    </div>

    <div class="ep-student-card">
        <div class="ep-info-box">
            <span class="ep-student-field-label">Student Name</span>
            <span class="ep-student-field-value"><?php echo htmlspecialchars($student['student_name']); ?></span>
        </div>
        <div class="ep-info-box">
            <span class="ep-student-field-label">Student ID</span>
            <span class="ep-student-field-value"><?php echo htmlspecialchars($student['student_id']); ?></span>
        </div>
        <div class="ep-info-box">
            <span class="ep-student-field-label">Company</span>
            <span class="ep-student-field-value"><?php echo htmlspecialchars($student['company_name'] ?? 'N/A'); ?></span>
        </div>
    </div>

    <form method="POST" id="evaluationForm">
        <div class="ep-main-grid">
            <div class="ep-rubric-card">
                <table class="ep-rubric-table">
                    <thead>
                        <tr class="ep-rubric-header">
                            <th>Assessment Criteria</th>
                            <th>Weight</th>
                            <th>Marks Given</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="ep-section-divider"><td colspan="3">Standard Criteria (10% each)</td></tr>
                        <?php 
                        $criteria = [
                            ['score_tasks', 'Undertaking Tasks/Projects', 10],
                            ['score_safety', 'Health & Safety', 10],
                            ['score_theory', 'Theoretical Knowledge', 10],
                            ['score_clarity', 'Language Clarity', 10]
                        ];
                        foreach($criteria as $c): ?>
                        <tr class="ep-rubric-row">
                            <td class="ep-rubric-name"><?php echo $c[1]; ?></td>
                            <td class="ep-rubric-weight"><?php echo $c[2]; ?>%</td>
                            <td class="ep-rubric-input-cell">
                                <div class="mark-stepper">
                                    <button type="button" class="step-btn" onclick="stepMark(this,-1)">−</button>
                                    <input type="number" name="<?php echo $c[0]; ?>" class="ep-rubric-input" 
                                           max="<?php echo $c[2]; ?>" min="0" step="0.5" required
                                           value="<?php echo $existing[$c[0]] ?? ''; ?>">
                                    <button type="button" class="step-btn" onclick="stepMark(this,1)">+</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <tr class="ep-section-divider"><td colspan="3">Extended Criteria (15% each)</td></tr>
                        <?php 
                        $extended = [
                            ['score_presentation', 'Presentation/Report', 15],
                            ['score_learning', 'Lifelong Learning', 15],
                            ['score_project_mgmt', 'Project Management', 15],
                            ['score_time_mgmt', 'Time Management', 15]
                        ];
                        foreach($extended as $e): ?>
                        <tr class="ep-rubric-row">
                            <td class="ep-rubric-name"><?php echo $e[1]; ?></td>
                            <td class="ep-rubric-weight"><?php echo $e[2]; ?>%</td>
                            <td class="ep-rubric-input-cell">
                                <div class="mark-stepper">
                                    <button type="button" class="step-btn" onclick="stepMark(this,-1)">−</button>
                                    <input type="number" name="<?php echo $e[0]; ?>" class="ep-rubric-input" 
                                           max="<?php echo $e[2]; ?>" min="0" step="0.5" required
                                           value="<?php echo $existing[$e[0]] ?? ''; ?>">
                                    <button type="button" class="step-btn" onclick="stepMark(this,1)">+</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="ep-right-panel">
                <div class="ep-score-card">
                    <span class="ep-score-label">Final Score</span>
                    <span id="live-total">0.0</span>
                    <div id="live-grade">Grade: —</div>
                    <div class="ep-progress-track">
                        <div class="ep-progress-fill" id="progress-bar"></div>
                    </div>
                </div>

                <div class="ep-feedback-card">
                    <label class="ep-feedback-label" for="feedback">Qualitative Feedback</label>
                    <textarea id="feedback" name="comments" class="ep-feedback-textarea" placeholder="Enter performance comments..."><?php echo htmlspecialchars($existing['comments'] ?? ''); ?></textarea>
                </div>

                <div class="ep-actions">
                    <button type="submit" class="ep-btn-final">Submit Results</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function stepMark(btn, dir) {
    const input = btn.parentElement.querySelector('.ep-rubric-input');
    const step  = parseFloat(input.getAttribute('step')) || 0.5;
    const max   = parseFloat(input.getAttribute('max'));
    const min   = parseFloat(input.getAttribute('min')) || 0;
    let val = parseFloat(input.value) || 0;
    val = Math.round((val + dir * step) * 100) / 100;
    val = Math.min(max, Math.max(min, val));
    input.value = val;
    input.dispatchEvent(new Event('input'));
}

document.addEventListener("DOMContentLoaded", function () {
    const inputs = document.querySelectorAll('.ep-rubric-input');
    const liveTotal = document.getElementById('live-total');
    const liveGrade = document.getElementById('live-grade');
    const progText = document.getElementById('progress-text');
    const progBar = document.getElementById('progress-bar');

    function recalculate() {
        let total = 0;
        let completed = 0;
        inputs.forEach(input => {
            let val = parseFloat(input.value);
            if (input.value !== '') {
                completed++;
                if (!isNaN(val)) total += val;
            }
        });
        progText.textContent = completed + ' / 8 Fields';
        progBar.style.width = (completed / 8) * 100 + '%';
        liveTotal.textContent = total.toFixed(1);
        
        if (total >= 80) setGrade('A', '#dcfce7', '#166534');
        else if (total >= 70) setGrade('B', '#dbeafe', '#1e40af');
        else if (total >= 60) setGrade('C', '#fef9c3', '#854d0e');
        else if (total >= 50) setGrade('D', '#ffedd5', '#c2410c');
        else if (total > 0) setGrade('F', '#fef2f2', '#b91c1c');
        else setGrade('—', '#f1f5f9', '#6b7280');
    }

    function setGrade(letter, bg, color) {
        liveGrade.textContent = 'Grade ' + letter;
        liveGrade.style.background = bg;
        liveGrade.style.color = color;
    }

    inputs.forEach(input => input.addEventListener('input', recalculate));
    recalculate(); 
});
</script>

<?php include("footer.php"); ?>