<?php
// Security & Logic First
session_start();
require("database.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Lecturer') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$current_staff_id = $_SESSION['user_id'];

// Fetch Lecturer's Full Name 
$name_query = mysqli_query($conn, "SELECT full_name FROM users WHERE user_id = '$current_staff_id'");
$user_row   = mysqli_fetch_assoc($name_query);
$display_name = $user_row['full_name'] ?? $_SESSION['username'];

// Fetch Aggregated Stats
$stats_q = mysqli_query($conn, "
    SELECT
        COUNT(DISTINCT i.internship_id) AS total,
        SUM(CASE
            WHEN (SELECT COUNT(*) FROM assessments
                  WHERE internship_id = i.internship_id
                    AND assessment_type = 'Academic') >= 2
            THEN 1 ELSE 0
        END) AS completed,
        (SELECT AVG(a2.total_mark)
         FROM assessments a2
         INNER JOIN internships i2 ON a2.internship_id = i2.internship_id
         WHERE (i2.lecturer_id = '$current_staff_id' OR i2.supervisor_id = '$current_staff_id')
           AND a2.assessment_type = 'Academic') AS avg_score
    FROM internships i
    WHERE (i.lecturer_id = '$current_staff_id' OR i.supervisor_id = '$current_staff_id')
");

$stats = mysqli_fetch_assoc($stats_q);

$total_assigned  = (int)($stats['total']     ?? 0);
$completed       = (int)($stats['completed'] ?? 0);
$pending         = $total_assigned - $completed;
$average_score   = number_format((float)($stats['avg_score'] ?? 0), 1);

include("header.php");
?>

<div class="dashboard-header">
    <div>
        <span class="stat-label">Academic Portal</span>
        <h1>Welcome, <?php echo htmlspecialchars($display_name); ?></h1>
    </div>
</div>

<div class="stats-grid-centered">

    <div class="stat-card">
        <div>
            <div class="lec-card-icon lec-card-icon--magenta">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 10h2"/><path d="M16 14h2"/><path d="M6.17 15a3 3 0 0 1 5.66 0"/>
                    <circle cx="9" cy="11" r="2"/><rect x="2" y="5" width="20" height="14" rx="2"/>
                </svg>
            </div>
            <div class="stat-card-header lec-card-header-mt">
                <h3 class="stat-card-title">Student Registry</h3>
                <span class="stat-card-badge lec-badge--magenta"><?php echo $total_assigned; ?> Assigned</span>
            </div>
            <p class="stat-card-desc">
                View all your assigned mentees with their current internship placement status.
            </p>
        </div>
        <a href="lecturer_view_my_students.php" class="btn-primary">View Student List</a>
    </div>

    <div class="stat-card">
        <div>
            <div class="lec-card-top-row">
                <div class="lec-card-icon lec-card-icon--pink-unique">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
                
                <div class="lec-avg-score">
                    <span class="lec-avg-label">Avg Score</span>
                    <span class="lec-avg-value" style="color: #e572a6;"><?php echo $average_score; ?>%</span>
                </div>
            </div>

            <div class="stat-card-header lec-card-header-mt">
                <h3 class="stat-card-title">Academic Evaluation</h3>
                <span class="stat-card-badge lec-badge--pink-unique"><?php echo $pending; ?> Pending</span>
            </div>
            <p class="stat-card-desc">
                Submit internship assessment marks and feedback for students who have not yet been evaluated.
            </p>
        </div>
        <a href="evaluate_list_lecturer.php" class="btn-primary">Evaluate Marks</a>
    </div>

</div>

<?php include("footer.php"); ?>