<?php
session_start();
require("database.php");

// 1. Security Check
if (!isset($_SESSION['role']) || trim($_SESSION['role']) !== 'Supervisor') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$current_staff_id = $_SESSION['user_id'];
$supervisor_name = $_SESSION['username'];

// 2. Fetch Stats (Filtered for 'Employer' type and supervisor ID)
$stats_q = mysqli_query($conn, "
    SELECT 
        COUNT(DISTINCT i.internship_id) as total,
        COUNT(DISTINCT a.internship_id) as completed,
        AVG(a.total_mark) as avg_score
    FROM internships i
    LEFT JOIN assessments a ON i.internship_id = a.internship_id AND a.assessment_type = 'Employer'
    WHERE i.supervisor_id = '$current_staff_id'
");

$stats = mysqli_fetch_assoc($stats_q);
$total_assigned = (int)($stats['total'] ?? 0);
$completed      = (int)($stats['completed'] ?? 0);
$pending        = $total_assigned - $completed;
$average_score  = number_format((float)($stats['avg_score'] ?? 0), 1);
$percent        = ($total_assigned > 0) ? round(($completed / $total_assigned) * 100) : 0;

include("header.php"); 
?>

<div class="dashboard-container">
    <div class="dashboard-header sup-border">
        <span class="sup-sub-label">Industry Supervisor Portal</span>
        <h1 class="sup-text-header">Welcome, <?php echo htmlspecialchars($supervisor_name); ?></h1>
        <p style="color: var(--text-muted);">Manage your interns and submit performance appraisals.</p>
    </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
        <div class="stat-card" style="padding: 12px 20px; border-left: 5px solid var(--primary-blue); min-height: 85px; display: flex; flex-direction: column; justify-content: center;">
            <span style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; line-height: 1;">Total Interns</span>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-top: 4px; line-height: 1;"><?php echo $total_assigned; ?></div>
        </div>

        <div class="stat-card" style="padding: 12px 20px; border-left: 5px solid #f59e0b; min-height: 85px; display: flex; flex-direction: column; justify-content: center;">
            <span style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; line-height: 1;">Pending Marking</span>
            <div style="font-size: 1.6rem; font-weight: 800; color: #f59e0b; margin-top: 4px; line-height: 1;"><?php echo $pending; ?></div>
        </div>

        <div class="stat-card" style="padding: 12px 20px; border-left: 5px solid #10b981; min-height: 85px; display: flex; flex-direction: column; justify-content: center;">
            <span style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; line-height: 1;">Avg. Performance</span>
            <div style="font-size: 1.6rem; font-weight: 800; color: #10b981; margin-top: 4px; line-height: 1;"><?php echo $average_score; ?>%</div>
        </div>
    </div>


    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <div class="stat-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="stat-card-header">
                    <h3 class="stat-card-title sup-text-header">Intern Directory</h3>
                    <span class="sup-badge">Management</span>
                </div>
                <p class="stat-card-desc">Access student profiles and industry supervisor contacts within your organization.</p>
            </div>
            <a href="supervisor_view_interns.php" class="btn-sup">Open Intern List</a>
        </div>

        <div class="stat-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="stat-card-header">
                    <h3 class="stat-card-title sup-text-header">Performance Appraisal</h3>
                    <span class="sup-badge"><?php echo $percent; ?>% Finished</span>
                </div>
                <p class="stat-card-desc">Submit industry feedback and evaluate professional conduct for your assigned interns.</p>
            </div>
            <a href="evaluate_list_supervisor.php" class="btn-sup">Evaluate Interns</a>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>