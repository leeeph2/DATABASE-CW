<?php
// 1. Start Session & Database Connection
session_start();
require("database.php");

// 2. Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Lecturer' && $_SESSION['role'] !== 'Supervisor')) {
    header("Location: index.php?error=unauthorized");
    exit();
}
$lecturer_id = $_SESSION['user_id'];

// 3. Search & Filter Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$filter_status = $_GET['status'] ?? "";

// Build base WHERE clause
$where_clause = " WHERE (i.lecturer_id = '$lecturer_id' OR i.supervisor_id = '$lecturer_id')";

if ($search !== "") {
    $where_clause .= " AND (s.student_id LIKE '%$search%' OR s.student_name LIKE '%$search%')";
}

// Fixed Status Filter Logic for the Filter Dropdown
if ($filter_status === "done") {
    $where_clause .= " AND (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) >= 2";
} elseif ($filter_status === "pending") {
    $where_clause .= " AND (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) = 1";
} elseif ($filter_status === "not_started") {
    $where_clause .= " AND (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) = 0";
}

// 4. Pagination Logic
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

// 5. Main Data Query
$query = "SELECT 
            s.student_id, 
            s.student_name, 
            i.company_name, 
            i.internship_id,
            (SELECT AVG(total_mark) FROM assessments WHERE internship_id = i.internship_id) as avg_marks,
            (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) as eval_count
          FROM students s 
          INNER JOIN internships i ON s.student_id = i.student_id " 
          . $where_clause . "
          ORDER BY s.student_name ASC 
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
$query_string = http_build_query(['search' => $search, 'status' => $filter_status]);

include("header.php");
?>

<div class="dashboard-container">
    <div class="page-header-flex" style="margin-bottom: 25px;">
        <div>
            <span class="stat-label" style="color: var(--primary-blue); font-weight: 700;">Student Directory</span>
            <h1 class="page-title-main" style="margin: 5px 0;">View Student Progression</h1>
            <a href="lecturer_dashboard.php" class="back-link" style="text-decoration: none; font-size: 0.85rem; color: var(--text-muted);">&larr; Back to Dashboard</a>
        </div>
    </div>

    <div class="stat-card filter-form-card" style="padding: 20px; margin-bottom: 25px;">
        <form method="GET" action="view_my_students.php" class="filter-form" style="display: flex; gap: 12px; align-items: center;">
            <div style="flex: 2;">
                <input type="text" name="search" class="filter-input" placeholder="Search Student ID or Name..." value="<?php echo htmlspecialchars($search); ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px;">
            </div>
            <div style="flex: 1;">
                <select name="status" class="filter-input" style="width: 100%; height: 42px; border-radius: 8px; border: 1px solid var(--border-color); padding: 0 10px; background: white;">
                    <option value="">All Statuses</option>
                    <option value="done" <?php echo ($filter_status == 'done') ? 'selected' : ''; ?>>Complete</option>
                    <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Pending (1/2)</option>
                    <option value="not_started" <?php echo ($filter_status == 'not_started') ? 'selected' : ''; ?>>Not Started</option>
                </select>
            </div>
            <button type="submit" class="btn-primary" style="white-space: nowrap; padding: 10px 25px; border-radius: 8px;">Search</button>
            <?php if($search !== "" || $filter_status !== ""): ?>
                <a href="view_my_students.php" style="color: var(--status-red); text-decoration: none; font-size: 0.85rem; font-weight: 700; margin-left: 10px;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="stat-card table-card" style="padding: 0; overflow: hidden;">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr class="table-head-row" style="background: #f8fafc; text-align: left; border-bottom: 2px solid #e2e8f0;">
                    <th class="table-th" style="padding: 15px 20px; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Student ID</th> 
                    <th class="table-th" style="padding: 15px 20px; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Name</th>       
                    <th class="table-th" style="padding: 15px 20px; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Completion</th>     
                    <th class="table-th" style="padding: 15px 20px; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Final Grade</th>
                    <th class="table-th" style="padding: 15px 20px; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; text-align: center;">Action</th>   
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="table-row" style="border-bottom: 1px solid #f1f5f9;">
                        <td class="table-td td-bold-dark" style="padding: 15px 20px; font-weight: 700;"><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td class="table-td td-bold" style="padding: 15px 20px; font-weight: 600;"><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td class="table-td" style="padding: 15px 20px;">
                            <?php if ($row['eval_count'] >= 2): ?>
                                <span class="badge-green" style="background: #dcfce7; color: #166534; padding: 5px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; border: 1px solid #bbf7d0;">COMPLETED</span>
                            <?php elseif ($row['eval_count'] == 1): ?>
                                <span class="badge-blue" style="background: #ffedd5; color: #9a3412; padding: 5px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; border: 1px solid #fed7aa;">PENDING (1/2)</span>
                            <?php else: ?>
                                <span class="badge-gray" style="background: #dcdcfc; color: #42445f; padding: 5px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; border: 1px solid #bbcdf7;">NOT STARTED</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-td" style="padding: 15px 20px;">
                            <?php if ($row['eval_count'] >= 2): ?>
                                <span style="font-weight: 800; color: var(--primary-blue);"><?php echo number_format($row['avg_marks'], 1); ?>%</span>
                            <?php else: ?>
                                <span style="color: #cbd5e1; font-size: 0.85rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-td" style="text-align: center; padding: 15px 20px;">
                            <?php if ($row['eval_count'] >= 2): ?>
                                <a href="view_final_report.php?id=<?php echo $row['internship_id']; ?>" 
                                   class="btn-primary" style="text-decoration: none; padding: 6px 18px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">
                                   VIEW REPORT
                                </a>
                            <?php else: ?>
                                <span style="color: #cbd5e1; font-size: 0.75rem; font-style: italic;">Locked</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="padding: 60px; text-align: center; color: var(--text-muted);">No records found matching your criteria.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background: #f8fafc; border-top: 1px solid var(--border-color);">
            <span class="pagination-info" style="font-size: 0.8rem; color: var(--text-muted);">
                Page <strong><?php echo $page; ?></strong> of <?php echo $total_pages; ?>
            </span>
            <div class="pagination-controls" style="display: flex; gap: 8px;">
                <a href="?<?php echo $query_string; ?>&page=<?php echo $page - 1; ?>" class="page-btn" style="padding: 6px 14px; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: #1e293b; background: white; font-size: 0.8rem; <?php echo ($page <= 1) ? 'opacity: 0.5; pointer-events: none;' : ''; ?>">Prev</a>
                <a href="?<?php echo $query_string; ?>&page=<?php echo $page + 1; ?>" class="page-btn" style="padding: 6px 14px; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: #1e293b; background: white; font-size: 0.8rem; <?php echo ($page >= $total_pages) ? 'opacity: 0.5; pointer-events: none;' : ''; ?>">Next</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include("footer.php"); ?>