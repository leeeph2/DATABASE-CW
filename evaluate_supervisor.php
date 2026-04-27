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
$assessor_type = 'Supervisor';

// 3. Fetch Student & Internship Details
if (!isset($_GET['id'])) {
    header("Location: evaluate_list_supervisor.php");
    exit();
}

$internship_id_url = mysqli_real_escape_string($conn, $_GET['id']);

$query = "SELECT s.student_name, s.student_id, i.internship_id, i.company_name, p.programme_name
          FROM students s
          INNER JOIN internships i ON s.student_id = i.student_id
          LEFT JOIN programmes p ON s.programme_id = p.programme_id
          WHERE i.internship_id = '$internship_id_url'
          AND i.supervisor_id = '$supervisor_id'";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: evaluate_list_supervisor.php?error=unauthorized_access");
    exit();
}

$student       = mysqli_fetch_assoc($result);
$internship_id = $student['internship_id'];

// Pre-fill logic
$check_stmt = $conn->prepare("SELECT * FROM assessments WHERE internship_id = ? AND assessment_type = 'Industry' LIMIT 1");
$check_stmt->bind_param("s", $internship_id);
$check_stmt->execute();
$existing = $check_stmt->get_result()->fetch_assoc();

// 4. Rubric Definition
$rubrics = [
    ['id' => 'score_tasks',        'label' => 'Undertaking Tasks / Projects',        'w' => 10],
    ['id' => 'score_safety',       'label' => 'Health & Safety Requirements',         'w' => 10],
    ['id' => 'score_theory',       'label' => 'Theoretical Knowledge Connectivity',   'w' => 10],
    ['id' => 'score_clarity',      'label' => 'Clarity of Language & Illustration',   'w' => 10],
    ['id' => 'score_presentation', 'label' => 'Presentation / Report',                'w' => 15],
    ['id' => 'score_learning',     'label' => 'Lifelong Learning Activities',         'w' => 15],
    ['id' => 'score_project_mgmt', 'label' => 'Project Management',                  'w' => 15],
    ['id' => 'score_time_mgmt',    'label' => 'Time Management',                     'w' => 15],
];

// 5. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    mysqli_begin_transaction($conn);
    try {
        $final_score = 0;
        $scores = [];
        foreach ($rubrics as $r) {
            $val = min(floatval($_POST[$r['id']] ?? 0), $r['w']);
            $scores[$r['id']] = $val;
            $final_score += $val;
        }
        $feedback = mysqli_real_escape_string($conn, $_POST['comments']);

       if ($existing) {
            $upd = $conn->prepare("UPDATE assessments SET assessor_id=?, score_tasks=?, score_safety=?, score_theory=?, score_presentation=?, score_clarity=?, score_learning=?, score_project_mgmt=?, score_time_mgmt=?, total_mark=?, comments=?, date_evaluated=NOW() WHERE internship_id=? AND assessment_type='Industry'");
            
            // Added one 's' at the end for $internship_id
            $upd->bind_param("sdddddddddss",
                $supervisor_id,
                $scores['score_tasks'], $scores['score_safety'], $scores['score_theory'],
                $scores['score_presentation'], $scores['score_clarity'], $scores['score_learning'],
                $scores['score_project_mgmt'], $scores['score_time_mgmt'],
                $final_score, $feedback, $internship_id
            );
            $upd->execute();
        } else {
            $new_id = "IND-" . strtoupper(bin2hex(random_bytes(4)));
            $ins = $conn->prepare("INSERT INTO assessments (assessment_id, internship_id, assessor_id, assessment_type, assessor_type, score_tasks, score_safety, score_theory, score_presentation, score_clarity, score_learning, score_project_mgmt, score_time_mgmt, total_mark, comments, date_evaluated) VALUES (?, ?, ?, 'Industry', 'Supervisor', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            // This one was already correct!
            $ins->bind_param("sssddddddddds",
                $new_id, $internship_id, $supervisor_id,
                $scores['score_tasks'], $scores['score_safety'], $scores['score_theory'],
                $scores['score_presentation'], $scores['score_clarity'], $scores['score_learning'],
                $scores['score_project_mgmt'], $scores['score_time_mgmt'],
                $final_score, $feedback
            );
            $ins->execute();
        }
        mysqli_commit($conn);
        header("Location: evaluate_list_supervisor.php?msg=evaluated&name=" . urlencode($student['student_name']));
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        die("Critical Error: " . $e->getMessage());
    }
}

include("header.php");
?>

<div id="eval-page" class="sup-theme">

    <div class="ep-header-row">
        <div>
            <a href="evaluate_list_supervisor.php" class="ep-breadcrumb">&larr; Back to Registry</a>
            <h1 class="ep-page-title">Industry Evaluation</h1>
        </div>
        <div style="text-align: right;">
            <span class="ep-progress-label">Fields Completed</span>
            <span id="prog-text">0 / 8</span>
        </div>
    </div>

    <div class="ep-student-card">
        <div style="display: flex; gap: 14px; align-items: center; flex: 1 1 100%;">
            <div style="width: 46px; height: 46px; background: #f0fdf4; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #059669; border: 1px solid #bbf7d0; font-size: 1.1rem; flex-shrink: 0;">
                <?php echo htmlspecialchars(mb_substr($student['student_name'], 0, 1)); ?>
            </div>
            <div>
                <span class="ep-student-field-value"><?php echo htmlspecialchars($student['student_name']); ?> &nbsp;(<?php echo htmlspecialchars($student['student_id']); ?>)</span>
                <span class="ep-student-field-label" style="margin-top: 3px;"><?php echo htmlspecialchars($student['programme_name'] ?? 'N/A'); ?></span>
            </div>
        </div>
        <div>
            <span class="ep-student-field-label">Company Placement</span>
            <span class="ep-student-field-value"><?php echo htmlspecialchars($student['company_name'] ?? 'N/A'); ?></span>
        </div>
    </div>

    <form method="POST" id="evalForm">
        <div class="ep-main-grid">

            <div class="ep-rubric-card">
                <table>
                    <thead class="ep-rubric-header">
                        <tr>
                            <th>Assessment Criteria</th>
                            <th style="text-align:center;">Max</th>
                            <th style="text-align:center;">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rubrics as $r): ?>
                        <tr class="ep-rubric-row">
                            <td class="ep-rubric-name"><?php echo htmlspecialchars($r['label']); ?></td>
                            <td class="ep-rubric-weight ep-w-high"><?php echo $r['w']; ?></td>
                            <td class="ep-rubric-input-cell">
                                <div class="mark-stepper">
                                    <button type="button" class="step-btn" data-step="-0.1">−</button>
                                    <input type="number"
                                           name="<?php echo $r['id']; ?>"
                                           class="ep-rubric-input score-input"
                                           data-max="<?php echo $r['w']; ?>"
                                           max="<?php echo $r['w']; ?>"
                                           min="0"
                                           step="0.1"
                                           placeholder="0.0"
                                           value="<?php echo isset($existing[$r['id']]) ? htmlspecialchars($existing[$r['id']]) : ''; ?>"
                                           required>
                                    <button type="button" class="step-btn" data-step="0.1">+</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="ep-right-panel">

                <div class="ep-score-card">
                    <span class="ep-score-card-label">Total Weighted Mark</span>
                    <span id="live-total">0.0</span>
                    <div id="grade-badge">Grade: —</div>
                    <div class="ep-progress-bar-track">
                        <div id="prog-bar" class="ep-progress-bar-fill"></div>
                    </div>
                </div>

                <div class="ep-feedback-card">
                    <span class="ep-feedback-label">Qualitative Feedback</span>
                    <textarea name="comments" class="ep-feedback-textarea"
                              placeholder="Provide feedback to justify the marks given..." required><?php echo htmlspecialchars($existing['comments'] ?? ''); ?></textarea>
                </div>

                <div class="ep-actions">
                    <button type="submit" class="ep-btn-final">
                        <?php echo $existing ? '✓ Update Evaluation' : '✓ Submit Evaluation'; ?>
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
(function () {
    const rubrics = <?php echo json_encode(array_map(fn($r) => ['id' => $r['id'], 'w' => $r['w']], $rubrics)); ?>;

    const liveTotal  = document.getElementById('live-total');
    const gradeBadge = document.getElementById('grade-badge');
    const progBar    = document.getElementById('prog-bar');
    const progText   = document.getElementById('prog-text');
    const inputs     = document.querySelectorAll('.score-input');

    function getGrade(score) {
        if (score >= 90) return { label: 'A+', bg: '#dcfce7', color: '#166534' };
        if (score >= 80) return { label: 'A',  bg: '#dcfce7', color: '#166534' };
        if (score >= 75) return { label: 'A-', bg: '#dcfce7', color: '#166534' };
        if (score >= 70) return { label: 'B+', bg: '#dbeafe', color: '#1e40af' };
        if (score >= 65) return { label: 'B',  bg: '#dbeafe', color: '#1e40af' };
        if (score >= 60) return { label: 'B-', bg: '#dbeafe', color: '#1e40af' };
        if (score >= 55) return { label: 'C+', bg: '#fef9c3', color: '#854d0e' };
        if (score >= 50) return { label: 'C',  bg: '#fef9c3', color: '#854d0e' };
        if (score >= 45) return { label: 'C-', bg: '#fef9c3', color: '#854d0e' };
        if (score >= 40) return { label: 'D',  bg: '#ffedd5', color: '#9a3412' };
        return { label: 'F', bg: '#fef2f2', color: '#b91c1c' };
    }

    function recalculate() {
        let total  = 0;
        let filled = 0;

        inputs.forEach(input => {
            const max = parseFloat(input.dataset.max);
            const val = parseFloat(input.value);

            if (!isNaN(val) && input.value !== '') {
                filled++;
                total += Math.min(val, max);
                if (val < 0 || val > max) {
                    input.classList.add('ep-invalid');
                    input.classList.remove('ep-valid');
                } else {
                    input.classList.add('ep-valid');
                    input.classList.remove('ep-invalid');
                }
            } else {
                input.classList.remove('ep-valid', 'ep-invalid');
            }
        });

        const rounded = Math.round(total * 10) / 10;
        liveTotal.textContent = rounded.toFixed(1);

        const grade = getGrade(rounded);
        gradeBadge.textContent    = 'Grade: ' + grade.label;
        gradeBadge.style.background = grade.bg;
        gradeBadge.style.color      = grade.color;

        progBar.style.width    = Math.min(rounded, 100) + '%';
        progText.textContent   = filled + ' / ' + inputs.length;
    }

    // Wire up +/- buttons using event delegation
    document.querySelectorAll('.mark-stepper').forEach(stepper => {
        stepper.addEventListener('click', function (e) {
            const btn = e.target.closest('.step-btn');
            if (!btn) return;
            const input   = stepper.querySelector('.ep-rubric-input');
            const step    = parseFloat(btn.dataset.step);
            const max     = parseFloat(input.max);
            const current = parseFloat(input.value) || 0;
            const next    = Math.round((current + step) * 10) / 10;
            input.value   = Math.min(Math.max(next, 0), max).toFixed(1);
            recalculate();
        });
    });

    inputs.forEach(input => input.addEventListener('input', recalculate));
    recalculate();
})();
</script>

<?php include("footer.php"); ?>