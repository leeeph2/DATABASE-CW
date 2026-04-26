<?php
// Security & Logic First
session_start(); 
require("database.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

// Fetch Dynamic Data
$students  = mysqli_num_rows(mysqli_query($conn, "SELECT student_id FROM students"));
$assessors = mysqli_num_rows(mysqli_query($conn, "SELECT user_id FROM users WHERE role IN ('Lecturer', 'Supervisor')"));
$reports   = mysqli_num_rows(mysqli_query($conn, "SELECT assessment_id FROM assessments"));

include("header.php"); 
?>

<div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
        <span class="stat-label">University of Nottingham Malaysia</span>
        <h1>Welcome, Admin</h1>
    </div>
</div>

<div class="stats-grid">

    <!-- Card 1 · Student Registry -->
    <div class="stat-card">
        <div>
            <div class="lec-card-icon lec-card-icon--blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-card-header" style="margin-top:20px;">
                <h3 class="stat-card-title">Student Registry</h3>
                <span class="stat-card-badge"><?php echo $students; ?> Records</span>
            </div>
            <p class="stat-card-desc">
                Manage student records, edit degree programmes, and monitor internship placement status.
            </p>
        </div>
        <a href="view_students.php" class="btn-primary">Manage Students</a>
    </div>

    <!-- Card 2 · Faculty Staffs -->
    <div class="stat-card">
        <div>
            <div class="lec-card-icon lec-card-icon--purple">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
            <div class="stat-card-header" style="margin-top:20px;">
                <h3 class="stat-card-title">Faculty Staffs</h3>
                <span class="stat-card-badge lec-badge--purple"><?php echo $assessors; ?> Users</span>
            </div>
            <p class="stat-card-desc">
                Organize faculty staffs, manage supervisor permissions, and assign student pairings.
            </p>
        </div>
        <a href="manage_assessors.php" class="btn-primary">Manage Staffs</a>
    </div>

    <!-- Card 3 · Academic Reports -->
    <div class="stat-card">
        <div>
            <div class="lec-card-icon lec-card-icon--green">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
            </div>
            <div class="stat-card-header" style="margin-top:20px;">
                <h3 class="stat-card-title">Academic Reports</h3>
                <span class="stat-card-badge lec-badge--green"><?php echo $reports; ?> Finalized</span>
            </div>
            <p class="stat-card-desc">
                Review submitted internship evaluations and generate formal academic performance reports.
            </p>
        </div>
        <a href="final_report.php" class="btn-primary">Review All Reports</a>
    </div>

</div>

<style>
    /* ── Icon block ── */
    .lec-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .lec-card-icon--blue   { background: rgba(37, 99, 235, 0.10); color: var(--primary-blue); }
    .lec-card-icon--purple { background: rgba(109, 40, 217, 0.10); color: #7c3aed; }
    .lec-card-icon--green  { background: rgba(34, 197, 94,  0.10); color: #16a34a; }

    /* ── Badge variants ── */
    .lec-badge--purple { background: rgba(109, 40, 217, 0.10); color: #6d28d9; }
    .lec-badge--green  { background: rgba(34,  197, 94,  0.10); color: #15803d; }

    /* ── Coloured stat values ── */
    .lec-stat-value--purple { color: #7c3aed !important; }
    .lec-stat-value--green  { color: #16a34a !important; }
</style>

<?php include("footer.php"); ?>