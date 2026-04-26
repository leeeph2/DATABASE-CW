<?php
// Security & Logic First
session_start();
require("database.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Lecturer') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$current_staff_id = $_SESSION['user_id'];

// Fetch Lecturer's Full Name for personalised welcome
$name_query = mysqli_query($conn, "SELECT full_name FROM users WHERE user_id = '$current_staff_id'");
$user_row   = mysqli_fetch_assoc($name_query);
$display_name = $user_row['full_name'] ?? $_SESSION['username'];

// Fetch Aggregated Stats for ACADEMIC evaluations only
$stats_q = mysqli_query($conn, "
    SELECT
        COUNT(DISTINCT i.internship_id)  AS total,
        COUNT(DISTINCT a.internship_id)  AS completed,
        AVG(a.total_mark)                AS avg_score
    FROM internships i
    LEFT JOIN assessments a
           ON i.internship_id   = a.internship_id
          AND a.assessment_type = 'Academic'
    WHERE i.lecturer_id = '$current_staff_id'
");

$stats = mysqli_fetch_assoc($stats_q);

$total_assigned  = (int)($stats['total']     ?? 0);
$completed       = (int)($stats['completed'] ?? 0);
$pending         = $total_assigned - $completed;
$average_score   = number_format((float)($stats['avg_score'] ?? 0), 1);
$progress_pct    = $total_assigned > 0 ? round(($completed / $total_assigned) * 100) : 0;

include("header.php");
?>

<div class="dashboard-header">
    <div>
        <span class="stat-label">University of Nottingham Malaysia</span>
        <h1>Welcome, <?php echo htmlspecialchars($display_name); ?></h1>
    </div>
    <span class="lec-date"><?php echo date('l, d F Y'); ?></span>
</div>

<div class="stats-grid-centered">

    <div class="stat-card">
        <div>
            <div class="lec-card-icon lec-card-icon--blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-card-header" style="margin-top: 20px;">
                <h3 class="stat-card-title">Student Registry</h3>
                <span class="stat-card-badge"><?php echo $total_assigned; ?> Assigned</span>
            </div>
            <p class="stat-card-desc">
                View all your assigned mentees, their contact details, degree programmes, and current internship placement status.
            </p>
        </div>
        <a href="lecturer_view_my_students.php" class="btn-primary">View Student List</a>
    </div>

    <div class="stat-card">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div class="lec-card-icon lec-card-icon--amber">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                
                <div style="text-align: right;">
                    <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); display: block; margin-bottom: 2px;">Avg Score</span>
                    <span style="font-size: 1.4rem; font-weight: 800; color: #d97706; line-height: 1;"><?php echo $average_score; ?>%</span>
                </div>
            </div>

            <div class="stat-card-header" style="margin-top: 20px;">
                <h3 class="stat-card-title">Academic Evaluation</h3>
                <span class="stat-card-badge lec-badge--amber"><?php echo $pending; ?> Pending</span>
            </div>
            <p class="stat-card-desc">
                Submit internship assessment marks and qualitative feedback for students who have not yet been evaluated.
            </p>
        </div>
        <a href="evaluate_list.php" class="btn-primary">Evaluate Marks</a>
    </div>

</div>

<style>
    /* ── Header date ── */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    .lec-date {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        padding-bottom: 4px;
    }

    /* ── Card icon block ── */
    .lec-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .lec-card-icon--blue {
        background: rgba(37, 99, 235, 0.1);
        color: var(--primary-blue);
    }
    .lec-card-icon--amber {
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
    }

    /* ── Badge variants ── */
    .lec-badge--amber {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }
    .lec-badge--green {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }
</style>

<?php include("footer.php"); ?>