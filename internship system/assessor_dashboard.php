<?php
session_start();
require("database.php");

// 1. Security Check: Only allow Assessors
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assessor') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$lecturer_id = $_SESSION['user_id'];
$lecturer_name = $_SESSION['username'];

// 2. Fetch Detailed Stats (Total, Completed, and Average Score)
$stats_q = mysqli_query($conn, "
    SELECT 
        COUNT(s.student_id) as total,
        SUM(CASE WHEN a.total_mark IS NOT NULL THEN 1 ELSE 0 END) as completed,
        AVG(a.total_mark) as avg_score
    FROM students s
    LEFT JOIN internships i ON s.student_id = i.student_id
    LEFT JOIN assessments a ON i.internship_id = a.internship_id
    WHERE s.supervisor_id = '$lecturer_id'
");
$stats = mysqli_fetch_assoc($stats_q);

$total_assigned = (int)($stats['total'] ?? 0);
$completed      = (int)($stats['completed'] ?? 0);
$average_score  = number_format((float)($stats['avg_score'] ?? 0), 1); 

include("header.php"); 
?>

<div class="dashboard-header">
    <span class="stat-label">University of Nottingham Malaysia</span>
    <h1>Welcome, Assessor</h1>
</div>

<div class="stats-grid-centered">
    
    <div class="stat-card">
        <div>
            <div class="stat-card-header">
                <h3 class="stat-card-title">Assigned Students</h3>
                <span class="stat-card-badge"><?php echo $total_assigned; ?> Records</span>
            </div>
            
            <p class="stat-card-desc">
                View all assigned student records, contact details, and monitor their current placement status.
            </p>
        </div>
        <a href="view_my_students.php" class="btn-primary">View Student List</a>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-header">
                <h3 class="stat-card-title">Internship Evaluations</h3>
                <span class="stat-card-badge"><?php echo $completed; ?> / <?php echo $total_assigned; ?> Done</span>
            </div>

            <p class="stat-card-desc">
                Input internship grades and provide qualitative feedback. The average score reflects all finalized assessments.
            </p>
            
            <div class="badge-green" style="display: inline-block; margin-top: 16px;">
                <span class="badge-green-label">Avg:</span>
                <span class="badge-green-value"><?php echo $average_score; ?>%</span>
            </div>
        </div>
        
        <a href="evaluate_list.php" class="btn-primary">Evaluate Marks</a>
    </div>

</div>

<?php include("footer.php"); ?>