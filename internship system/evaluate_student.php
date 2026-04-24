<?php
session_start();
require("database.php");

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assessor') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$lecturer_id = $_SESSION['user_id'];

// 2. Fetch Student Details
if (isset($_GET['id'])) {
    $student_id = mysqli_real_escape_string($conn, $_GET['id']);

    $query = "SELECT s.student_name, s.student_id, i.internship_id, i.company_name 
              FROM students s 
              LEFT JOIN internships i ON s.student_id = i.student_id 
              WHERE s.student_id = '$student_id' AND s.supervisor_id = '$lecturer_id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 0) {
        header("Location: evaluate_list.php");
        exit();
    }

    $student = mysqli_fetch_assoc($result);
    $internship_id = $student['internship_id'];

    if (empty($internship_id)) {
        header("Location: evaluate_list.php?error=no_internship");
        exit();
    }
} else {
    header("Location: evaluate_list.php");
    exit();
}

// 3. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($internship_id)) {
        header("Location: evaluate_list.php?error=no_internship");
        exit();
    }

    $m1 = min(floatval($_POST['m1'] ?? 0), 10);
    $m2 = min(floatval($_POST['m2'] ?? 0), 10);
    $m3 = min(floatval($_POST['m3'] ?? 0), 10);
    $m4 = min(floatval($_POST['m4'] ?? 0), 15);
    $m5 = min(floatval($_POST['m5'] ?? 0), 10);
    $m6 = min(floatval($_POST['m6'] ?? 0), 15);
    $m7 = min(floatval($_POST['m7'] ?? 0), 15);
    $m8 = min(floatval($_POST['m8'] ?? 0), 15);

    $final_score = $m1 + $m2 + $m3 + $m4 + $m5 + $m6 + $m7 + $m8;
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);

    $check = mysqli_query($conn, "SELECT assessment_id FROM assessments WHERE internship_id = '$internship_id'");

    if (mysqli_num_rows($check) > 0) {
        $sql = "UPDATE assessments SET total_mark = '$final_score', comments = '$feedback' WHERE internship_id = '$internship_id'";
    } else {
        $ass_id = "EVAL-" . time();
        $sql = "INSERT INTO assessments (assessment_id, internship_id, total_mark, comments) VALUES ('$ass_id', '$internship_id', '$final_score', '$feedback')";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: evaluate_list.php?msg=evaluated");
        exit();
    } else {
        die("Database Error: " . mysqli_error($conn));
    }
}

include("header.php");
?>

<div id="eval-page">
    <div class="ep-header-row">
        <div>
            <a href="evaluate_list.php" class="ep-breadcrumb">&larr; Back to Evaluation List</a>
            <h1 class="ep-page-title">Student Evaluation</h1>
        </div>
        <div>
            <span class="ep-progress-label">Completion Progress</span>
            <span id="progress-text">0 / 8 Fields</span>
        </div>
    </div>

    <div class="ep-student-card">
        <div>
            <span class="ep-student-field-label">Student Name</span>
            <span class="ep-student-field-value"><?php echo htmlspecialchars($student['student_name']); ?></span>
        </div>
        <div>
            <span class="ep-student-field-label">Student ID</span>
            <span class="ep-student-field-value"><?php echo htmlspecialchars($student['student_id']); ?></span>
        </div>
        <div>
            <span class="ep-student-field-label">Company</span>
            <span class="ep-student-field-value"><?php echo htmlspecialchars($student['company_name'] ?? 'No Company Assigned'); ?></span>
        </div>
    </div>

    <form method="POST" id="evaluationForm">
        <div class="ep-main-grid">
            <div class="ep-rubric-card">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr class="ep-rubric-header">
                            <th>Assessment Criteria</th>
                            <th style="text-align:center;">Weight</th>
                            <th style="text-align:center;">Marks Given</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="ep-section-divider"><td colspan="3">Standard Criteria &mdash; 10% each</td></tr>
                        <tr class="ep-rubric-row">
                            <td class="ep-rubric-name">Undertaking Tasks/Projects</td>
                            <td class="ep-rubric-weight">10%</td>
                            <td class="ep-rubric-input-cell">
                                <div class="mark-stepper">
                                    <button type="button" class="step-btn" onclick="stepMark(this,-1)">−</button>
                                    <input type="number" name="m1" class="ep-rubric-input" max="10" min="0" step="0.5" placeholder="0" required>
                                    <button type="button" class="step-btn" onclick="stepMark(this,1)">+</button>
                                </div>
                            </td>
                        </tr>
                        <tr class="ep-rubric-row">
                            <td class="ep-rubric-name">Health &amp; Safety</td>
                            <td class="ep-rubric-weight">10%</td>
                            <td class="ep-rubric-input-cell">
                                <div class="mark-stepper">
                                    <button type="button" class="step-btn" onclick="stepMark(this,-1)">−</button>
                                    <input type="number" name="m2" class="ep-rubric-input" max="10" min="0" step="0.5" placeholder="0" required>
                                    <button type="button" class="step-btn" onclick="stepMark(this,1)">+</button>
                                </div>
                            </td>
                        </tr>
                        <tr class="ep-rubric-row">
                            <td class="ep-rubric-name">Theoretical Knowledge</td>
                            <td class="ep-rubric-weight">10%</td>
                            <td class="ep-rubric-input-cell">
                                <div class="mark-stepper">
                                    <button type="button" class="step-btn" onclick="stepMark(this,-1)">−</button>
                                    <input type="number" name="m3" class="ep-rubric-input" max="10" min="0" step="0.5" placeholder="0" required>
                                    <button type="button" class="step-btn" onclick="stepMark(this,1)">+</button>
                                </div>
                            </td>
                        </tr>
                        <tr class="ep-rubric-row">
                            <td class="ep-rubric-name">Language Clarity</td>
                            <td class="ep-rubric-weight">10%</td>
                            <td class="ep-rubric-input-cell">
                                <div class="mark-stepper">
                                    <button type="button" class="step-btn" onclick="stepMark(this,-1)">−</button>
                                    <input type="number" name="m5" class="ep-rubric-input" max="10" min="0" step="0.5" placeholder="0" required>
                                    <button type="button" class="step-btn" onclick="stepMark(this,1)">+</button>
                                </div>
                            </td>
                        </tr>
                        <tr class="ep-section-divider"><td colspan="3">Extended Criteria &mdash; 15% each</td></tr>
                        <tr class="ep-rubric-row">
                            <td class="ep-rubric-name">Report Writing</td>
                            <td class="ep-rubric-weight ep-w-high">15%</td>
                            <td class="ep-rubric-input-cell">
                                <div class="mark-stepper">
                                    <button type="button" class="step-btn" onclick="stepMark(this,-1)">−</button>
                                    <input type="number" name="m4" class="ep-rubric-input" max="15" min="0" step="0.5" placeholder="0" required>
                                    <button type="button" class="step-btn" onclick="stepMark(this,1)">+</button>
                                </div>
                            </td>
                        </tr>
                        <tr class="ep-rubric-row">
                            <td class="ep-rubric-name">Lifelong Learning</td>
                            <td class="ep-rubric-weight ep-w-high">15%</td>
                            <td class="ep-rubric-input-cell">
                                <div class="mark-stepper">
                                    <button type="button" class="step-btn" onclick="stepMark(this,-1)">−</button>
                                    <input type="number" name="m6" class="ep-rubric-input" max="15" min="0" step="0.5" placeholder="0" required>
                                    <button type="button" class="step-btn" onclick="stepMark(this,1)">+</button>
                                </div>
                            </td>
                        </tr>
                        <tr class="ep-rubric-row">
                            <td class="ep-rubric-name">Project Management</td>
                            <td class="ep-rubric-weight ep-w-high">15%</td>
                            <td class="ep-rubric-input-cell">
                                <div class="mark-stepper">
                                    <button type="button" class="step-btn" onclick="stepMark(this,-1)">−</button>
                                    <input type="number" name="m7" class="ep-rubric-input" max="15" min="0" step="0.5" placeholder="0" required>
                                    <button type="button" class="step-btn" onclick="stepMark(this,1)">+</button>
                                </div>
                            </td>
                        </tr>
                        <tr class="ep-rubric-row">
                            <td class="ep-rubric-name">Time Management</td>
                            <td class="ep-rubric-weight ep-w-high">15%</td>
                            <td class="ep-rubric-input-cell">
                                <div class="mark-stepper">
                                    <button type="button" class="step-btn" onclick="stepMark(this,-1)">−</button>
                                    <input type="number" name="m8" class="ep-rubric-input" max="15" min="0" step="0.5" placeholder="0" required>
                                    <button type="button" class="step-btn" onclick="stepMark(this,1)">+</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="ep-right-panel">
                <div class="ep-score-card">
                    <span class="ep-score-card-label">Calculated Final Score</span>
                    <span id="live-total">0.0</span>
                    <div id="live-grade">Grade: &mdash;</div>
                    <div class="ep-progress-bar-track">
                        <div class="ep-progress-bar-fill" id="progress-bar"></div>
                    </div>
                </div>
                <div class="ep-feedback-card">
                    <label class="ep-feedback-label" for="feedback">Qualitative Feedback</label>
                    <textarea id="feedback" name="feedback" class="ep-feedback-textarea" required></textarea>
                </div>
                <div class="ep-actions">
                    <button type="submit" class="ep-btn-final">Submit Final Results</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function stepMark(btn, dir) {
    const input = btn.parentElement.querySelector('.ep-rubric-input');
    const step  = parseFloat(input.getAttribute('step'))  || 0.5;
    const max   = parseFloat(input.getAttribute('max'));
    const min   = parseFloat(input.getAttribute('min'))   || 0;
    let val = parseFloat(input.value);
    if (isNaN(val)) val = 0;
    val = Math.round((val + dir * step) * 100) / 100; // avoid float drift
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
            const max = parseFloat(input.getAttribute('max'));
            if (input.value !== '') {
                completed++;
                if (val > max) { val = max; input.value = max; }
                if (val < 0) { val = 0; input.value = 0; }
            }
            if (!isNaN(val)) total += val;
        });
        progText.textContent = completed + ' / 8 Fields';
        progBar.style.width = (completed / 8) * 100 + '%';
        liveTotal.textContent = total.toFixed(1);
        
        if (total >= 80) { setGrade('A', '#dcfce7', '#166534'); }
        else if (total >= 70) { setGrade('B', '#dbeafe', '#1e40af'); }
        else if (total >= 60) { setGrade('C', '#fef9c3', '#854d0e'); }
        else if (total >= 50) { setGrade('D', '#ffedd5', '#c2410c'); }
        else if (total > 0) { setGrade('F', '#fef2f2', '#b91c1c'); }
        else { setGrade('—', '#f1f5f9', '#6b7280'); }
    }

    function setGrade(letter, bg, color) {
        liveGrade.textContent = 'Grade ' + letter;
        liveGrade.style.background = bg;
        liveGrade.style.color = color;
    }

    inputs.forEach(input => input.addEventListener('input', recalculate));
});
</script>

<?php include("footer.php"); ?>