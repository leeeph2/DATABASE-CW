<?php
// Security & Logic First
session_start(); 
require("database.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

// Fetch Dynamic Data for Phoon Le-Ee's System
$students = mysqli_num_rows(mysqli_query($conn, "SELECT student_id FROM students"));
$assessors = mysqli_num_rows(mysqli_query($conn, "SELECT user_id FROM users WHERE role = 'Assessor'"));
$reports = mysqli_num_rows(mysqli_query($conn, "SELECT assessment_id FROM assessments"));

include("header.php"); 
?>

<div class="dashboard-header">
    <span class="stat-label">University of Nottingham Malaysia</span>
    <h1>Welcome,Admin</h1>
</div>

<div class="stats-grid">
    
    <div class="stat-card">
        <div>
            <div class="stat-card-header">
                <h3 class="stat-card-title">Student Registry</h3>
                <span class="stat-card-badge"><?php echo $students; ?> Records</span>
            </div>
            <p class="stat-card-desc">
                Manage student records, edit degree programmes, and monitor internship placement status.
            </p>
        </div>
        <a href="view_students.php" class="btn-primary">Manage Students</a>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-header">
                <h3 class="stat-card-title">Faculty Assessors</h3>
                <span class="stat-card-badge"><?php echo $assessors; ?> Users</span>
            </div>
            <p class="stat-card-desc">
                Organize faculty assessors, manage supervisor permissions, and assign student pairings.
            </p>
        </div>
        <a href="manage_assessors.php" class="btn-primary">Manage Assessors</a>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-header">
                <h3 class="stat-card-title">Academic Reports</h3>
                <span class="stat-card-badge"><?php echo $reports; ?> Finalized</span>
            </div>
            <p class="stat-card-desc">
                Review submitted internship evaluations and generate formal academic performance reports.
            </p>
        </div>
        <a href="final_report.php" class="btn-primary">Review All Reports</a>
    </div>

</div>


<?php include("footer.php"); ?>