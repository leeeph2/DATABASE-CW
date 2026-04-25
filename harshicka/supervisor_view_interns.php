<?php
// 1. Start Session & Database Connection
session_start();
require("database.php");

// 2. Security Check (Matches your Dashboard logic)
if (!isset($_SESSION['user_id']) || trim($_SESSION['role']) !== 'Supervisor') {
    header("Location: index.php?error=unauthorized");
    exit();
}
$supervisor_id = $_SESSION['user_id'];

// 3. Search & Filter Logic (Mirrored exactly from lecturer_view_my_students.php)
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$filter_status = $_GET['status'] ?? "";

// Build base WHERE clause restricted to this Supervisor
$where_clause = " WHERE i.supervisor_id = '$supervisor_id'";

if ($search !== "") {
    $where_clause .= " AND (s.student_id LIKE '%$search%' OR s.student_name LIKE '%$search%' OR i.internship_id LIKE '%$search%')";
}

// Fixed Status Filter Logic (Mirrored exactly from Lecturer file logic)
if ($filter_status === "done") {
    $where_clause .= " AND (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) >= 2";
} elseif ($filter_status === "pending") {
    $where_clause .= " AND (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) = 1";
} elseif ($filter_status === "not_started") {
    $where_clause .= " AND (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) = 0";
}

// 4. Pagination Logic (Mirrored exactly)
$limit = 10; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query = "SELECT COUNT(DISTINCT s.student_id) as total 
                FROM students s 
                INNER JOIN internships i ON s.student_id = i.student_id" 
                . $where_clause;

$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// 5. Main Data Query (Adding internship_id and separating student_name)
$query = "SELECT 
            i.internship_id, 
            s.student_name, 
            p.programme_name,
            i.internship_status,
            (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) as eval_count
          FROM students s 
          INNER JOIN internships i ON s.student_id = i.student_id 
          LEFT JOIN programmes p ON s.programme_id = p.programme_id " 
          . $where_clause . "
          ORDER BY s.student_name ASC 
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
$query_string = http_build_query(['search' => $search, 'status' => $filter_status]);

include("header.php");
?>

<div class="dashboard-container">
    <div class="dashboard-header sup-border">
        <span class="sup-sub-label">Industry Management</span>
        <h1 class="page-title-main sup-text-header">My Assigned Interns</h1>
        <a href="supervisor_dashboard.php" class="back-link" style="text-decoration: none; font-size: 0.9rem; color: var(--text-muted);">&larr; Back to Dashboard</a>
    </div>

    <div class="stat-card filter-form-card" style="padding: 20px; margin-bottom: 25px;">
        <form method="GET" action="supervisor_view_interns.php" class="filter-form" style="display: flex; gap: 12px; align-items: center;">
            <div style="flex: 2;">
                <input type="text" name="search" class="filter-input" placeholder="Search Name or ID..." value="<?php echo htmlspecialchars($search); ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;">
            </div>
            <div style="flex: 1;">
                <select name="status" class="filter-input" style="width: 100%; height: 42px; border-radius: 8px; border: 1px solid var(--border-color); padding: 0 10px; background: white;">
                    <option value="">All Statuses</option>
                    <option value="done" <?php echo ($filter_status == 'done') ? 'selected' : ''; ?>>Complete</option>
                    <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Ongoing</option>
                    <option value="not_started" <?php echo ($filter_status == 'not_started') ? 'selected' : ''; ?>>Not Started</option>
                </select>
            </div>
            <button type="submit" class="btn-primary" style="white-space: nowrap; padding: 10px 25px; border-radius: 8px;">Search</button>
            <?php if($search !== "" || $filter_status !== ""): ?>
                <a href="supervisor_view_interns.php" style="color: var(--status-red); text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-left: 10px;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="stat-card" style="padding: 0; overflow: hidden; border: 1px solid var(--border-color); border-radius: 12px; background: white;">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid var(--border-color);">
                    <th style="padding: 15px 20px; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);">Internship ID</th>
                    <th style="padding: 15px 20px; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);">Intern Name</th>
                    <th style="padding: 15px 20px; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);">Programme</th>
                    <th style="padding: 15px 20px; text-align: right; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 15px 20px; font-weight: 700; color: #1e293b; font-family: monospace;"><?php echo htmlspecialchars($row['internship_id']); ?></td>
                        <td style="padding: 15px 20px; font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td style="padding: 15px 20px; font-size: 0.9rem; color: #475569;"><?php echo htmlspecialchars($row['programme_name'] ?? 'Unassigned'); ?></td>
                        <td style="padding: 15px 20px; text-align: right;">
                            <?php if ($row['eval_count'] >= 2): ?>
                                <span class="badge-green" style="background: #dcfce7; color: #166534; padding: 5px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; border: 1px solid #bbf7d0;">COMPLETED</span>
                            <?php elseif ($row['eval_count'] == 1): ?>
                                <span class="badge-blue" style="background: #ffedd5; color: #9a3412; padding: 5px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; border: 1px solid #fed7aa;">PENDING</span>
                            <?php else: ?>
                                <span class="badge-gray" style="background: #dcdcfc; color: #42445f; padding: 5px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; border: 1px solid #9b98e4;">NOT STARTED</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="padding: 40px; text-align: center; color: var(--text-muted);">No assigned interns found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background: #f8fafc; border-top: 1px solid var(--border-color);">
            <span style="font-size: 0.8rem; color: var(--text-muted);">Page <strong><?php echo $page; ?></strong> of <?php echo $total_pages; ?></span>
            <div style="display: flex; gap: 8px;">
                <a href="?<?php echo $query_string; ?>&page=<?php echo $page - 1; ?>" class="page-btn" style="padding: 6px 14px; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: #1e293b; background: white; font-size: 0.8rem; <?php echo ($page <= 1) ? 'opacity: 0.5; pointer-events: none;' : ''; ?>">Prev</a>
                <a href="?<?php echo $query_string; ?>&page=<?php echo $page + 1; ?>" class="page-btn" style="padding: 6px 14px; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: #1e293b; background: white; font-size: 0.8rem; <?php echo ($page >= $total_pages) ? 'opacity: 0.5; pointer-events: none;' : ''; ?>">Next</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include("footer.php"); ?>