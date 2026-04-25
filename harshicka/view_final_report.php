<?php
session_start();
require("database.php");

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 2. Fetch Report Data
if (isset($_GET['id'])) {
    $internship_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Get Student and Internship info
    $info_q = mysqli_query($conn, "
        SELECT s.student_name, s.student_id, i.company_name, i.internship_id
        FROM internships i
        JOIN students s ON i.student_id = s.student_id
        WHERE i.internship_id = '$internship_id'
    ");
    $info = mysqli_fetch_assoc($info_q);

    // Get both Industry and Academic assessments
    $assess_q = mysqli_query($conn, "SELECT * FROM assessments WHERE internship_id = '$internship_id'");
    $evals = [];
    while ($row = mysqli_fetch_assoc($assess_q)) {
        $evals[$row['assessment_type']] = $row;
    }
} else {
    die("No Internship ID provided.");
}

include("header.php");
?>

<div class="dashboard-container">
    <div class="dashboard-header" style="border-bottom: 2px solid var(--primary-blue); margin-bottom: 30px; padding-bottom: 20px;">
        <h1 class="page-title-main">Final Performance Report</h1>
        <p style="color: var(--text-muted);">Internship ID: #<?php echo $info['internship_id']; ?></p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;">
        <div class="stat-card">
            <h3 style="color: var(--primary-blue); margin-bottom: 15px;">Student Details</h3>
            <p><strong>Name:</strong> <?php echo $info['student_name']; ?></p>
            <p><strong>ID:</strong> <?php echo $info['student_id']; ?></p>
            <p><strong>Company:</strong> <?php echo $info['company_name']; ?></p>
        </div>

        <div class="stat-card" style="text-align: center; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="margin-bottom: 10px;">Overall Result</h3>
            <?php 
                $final_avg = 0;
                if(isset($evals['Academic']) && isset($evals['Industry'])) {
                    $final_avg = ($evals['Academic']['total_mark'] + $evals['Industry']['total_mark']) / 2;
                }
            ?>
            <div style="font-size: 3rem; font-weight: 800; color: var(--primary-blue);"><?php echo number_format($final_avg, 1); ?>%</div>
            <p style="font-weight: 600; color: var(--text-muted);">Combined Industry & Academic Average</p>
        </div>
    </div>

    <div class="stat-card">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid #eee;">
                    <th style="padding: 15px;">Criteria</th>
                    <th style="padding: 15px;">Academic (Lecturer)</th>
                    <th style="padding: 15px;">Industry (Supervisor)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $criteria_map = [
                    'score_tasks' => 'Tasks/Projects',
                    'score_safety' => 'Health & Safety',
                    'score_theory' => 'Theoretical Knowledge',
                    'score_presentation' => 'Presentation/Report',
                    'score_clarity' => 'Language Clarity',
                    'score_learning' => 'Lifelong Learning',
                    'score_project_mgmt' => 'Project Mgmt',
                    'score_time_mgmt' => 'Time Mgmt'
                ];

                foreach ($criteria_map as $col => $label): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px; font-weight: 600;"><?php echo $label; ?></td>
                        <td style="padding: 12px;"><?php echo $evals['Academic'][$col] ?? '—'; ?></td>
                        <td style="padding: 12px;"><?php echo $evals['Industry'][$col] ?? '—'; ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr style="background: #f8fafc; font-weight: 800;">
                    <td style="padding: 15px;">TOTAL MARK (100%)</td>
                    <td style="padding: 15px; color: var(--primary-blue);"><?php echo $evals['Academic']['total_mark'] ?? 'Pending'; ?></td>
                    <td style="padding: 15px; color: var(--primary-blue);"><?php echo $evals['Industry']['total_mark'] ?? 'Pending'; ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px; display: flex; gap: 15px;">
        <button onclick="window.print()" class="btn-primary">Print Report</button>
        <a href="supervisor_dashboard.php" class="btn-sup" style="background: #64748b; text-decoration: none;">Return to Dashboard</a>
    </div>
</div>

<?php include("footer.php"); ?>