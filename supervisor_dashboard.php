<?php
// Security & Logic First
session_start();
require("database.php");

// Security Check: Only allow Supervisors
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Supervisor') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$current_staff_id = $_SESSION['user_id'];

// Fetch Supervisor's Full Name for personalised welcome
$name_query   = mysqli_query($conn, "SELECT full_name FROM users WHERE user_id = '$current_staff_id'");
$user_row     = mysqli_fetch_assoc($name_query);
$display_name = $user_row['full_name'] ?? $_SESSION['username'];

// Fetch Aggregated Stats — Supervisor assessments only
$stats_q = mysqli_query($conn, "
    SELECT
        COUNT(DISTINCT i.internship_id) AS total,
        SUM(CASE WHEN (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) >= 2 THEN 1 ELSE 0 END) AS completed,
        AVG(a.total_mark) AS avg_score
    FROM internships i
    LEFT JOIN assessments a
           ON i.internship_id = a.internship_id
          AND a.assessor_id   = '$current_staff_id'
    WHERE i.supervisor_id = '$current_staff_id'
");

$stats = mysqli_fetch_assoc($stats_q);

$total_assigned = (int)($stats['total']     ?? 0);
$completed      = (int)($stats['completed'] ?? 0);
$pending        = $total_assigned - $completed;
$average_score  = number_format((float)($stats['avg_score'] ?? 0), 1);

include("header.php");
?>

<div class="dashboard-header">
    <div>
        <span class="stat-label">University of Nottingham Malaysia</span>
        <h1 class="sup-dashboard-title">Welcome, <?php echo htmlspecialchars($display_name); ?></h1>
    </div>
</div>

<div class="stats-grid-centered">

    <div class="stat-card">
        <div>
            <div class="sup-card-top-row">
                <div class="sup-card-icon sup-card-icon--red">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
            </div>
            
            <div class="sup-card-header">
                <h3 class="sup-card-title">Intern List</h3>
                <span class="stat-card-badge sup-badge sup-badge--red"><?php echo $total_assigned; ?> Assigned</span>
            </div>
            
            <p class="stat-card-desc sup-card-desc">
                View all interns under your supervision, their programmes, and current internship placement status.
            </p>
        </div>
        <a href="supervisor_view_interns.php" class="btn-sup-green">View Intern List</a>
    </div>

    <div class="stat-card">
        <div>
            <div class="sup-card-top-row">
                <div class="sup-card-icon sup-card-icon--yellow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>
                    </svg>
                </div>
                <div class="sup-avg-score">
                    <span class="sup-avg-label">Avg Score</span>
                    <span class="sup-avg-value" style="color: #ca8a04;"><?php echo $average_score; ?>%</span>
                </div>
            </div>

            <div class="sup-card-header">
                <h3 class="sup-card-title">Industry Evaluation</h3>
                <span class="stat-card-badge sup-badge sup-badge--yellow"><?php echo $pending; ?> Pending</span>
            </div>
            
            <p class="stat-card-desc sup-card-desc">
                Submit workplace performance assessments and qualitative feedback for students assigned to your company.
            </p>
        </div>
        <a href="evaluate_list_supervisor.php" class="btn-sup-green">Evaluate Interns</a>
    </div>

</div>

<?php include("footer.php"); ?>