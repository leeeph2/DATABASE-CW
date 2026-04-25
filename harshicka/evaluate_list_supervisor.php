<?php
// 1. Start Session & Database Connection
session_start();
require("database.php");

// 2. Security Check (Optimized for Supervisor)
if (!isset($_SESSION['user_id']) || trim($_SESSION['role']) !== 'Supervisor') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$current_supervisor_id = $_SESSION['user_id'];

// 3. Search & Filter Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$status_filter = $_GET['status_filter'] ?? ""; 
$company_filter = $_GET['company_filter'] ?? "";
$sort = $_GET['sort'] ?? "name_asc";

// Build base WHERE clause restricted to the current Supervisor
$where_clause = " WHERE i.supervisor_id = '$current_supervisor_id'";

if ($search !== "") {
    $where_clause .= " AND (i.internship_id LIKE '%$search%' OR s.student_name LIKE '%$search%')";
}

if ($company_filter === "pending") {
    $where_clause .= " AND (i.company_name IS NULL OR i.company_name = '')";
} elseif ($company_filter !== "") {
    $where_clause .= " AND i.company_name = '$company_filter'";
}

// Logic for Status Filter: Checks if an 'Employer' type assessment exists
if ($status_filter === "done") {
    $where_clause .= " AND EXISTS (SELECT 1 FROM assessments WHERE internship_id = i.internship_id AND assessment_type = 'Employer')";
} elseif ($status_filter === "pending") {
    $where_clause .= " AND NOT EXISTS (SELECT 1 FROM assessments WHERE internship_id = i.internship_id AND assessment_type = 'Employer')";
}

// Sorting logic
$order_by = "s.student_name ASC";
if ($sort === "name_desc") $order_by = "s.student_name DESC";
if ($sort === "id_asc") $order_by = "i.internship_id ASC";
if ($sort === "id_desc") $order_by = "i.internship_id DESC";

// 4. Pagination
$limit = 10; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query = "SELECT COUNT(DISTINCT i.internship_id) as total 
                FROM internships i 
                INNER JOIN students s ON i.student_id = s.student_id" . $where_clause;
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'] ?? 0;
$total_pages = ceil($total_records / $limit);

// 5. Main Data Query (Swapped student_id for internship_id)
$query = "SELECT i.internship_id, s.student_name, i.company_name, 
          (SELECT assessment_id FROM assessments WHERE internship_id = i.internship_id AND assessment_type = 'Employer' LIMIT 1) as is_done
          FROM internships i
          INNER JOIN students s ON i.student_id = s.student_id " 
          . $where_clause . 
          " ORDER BY $order_by LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
$query_params = $_GET;
unset($query_params['page']);
$query_string = http_build_query($query_params);

include("header.php");
?>

<div class="dashboard-container">
    <div class="page-header-flex">
        <div>
            <span class="stat-label" style="color: var(--primary-blue); font-weight: 700;">Evaluation Portal</span>
            <h1 class="page-title-main">Select Intern to Evaluate</h1>
            <a href="supervisor_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
        </div>
    </div>

    <div class="stat-card filter-form-card">
        <form method="GET" action="evaluate_list_supervisor.php" class="filter-form filter-form-compact">
            <div class="filter-form-group">
                <input type="text" name="search" class="filter-input" placeholder="Search Intern ID or Name..." value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="filter-select-group">
                <select name="status_filter" class="filter-select">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending Evaluation</option>
                    <option value="done" <?php echo ($status_filter == 'done') ? 'selected' : ''; ?>>Evaluated</option>
                </select>
            </div>

            <div class="filter-select-group">
                <select name="sort" class="filter-select">
                    <option value="name_asc" <?php echo ($sort == 'name_asc') ? 'selected' : ''; ?>>Name (A → Z)</option>
                    <option value="name_desc" <?php echo ($sort == 'name_desc') ? 'selected' : ''; ?>>Name (Z → A)</option>
                    <option value="id_asc" <?php echo ($sort == 'id_asc') ? 'selected' : ''; ?>>Intern ID (Asc)</option>
                    <option value="id_desc" <?php echo ($sort == 'id_desc') ? 'selected' : ''; ?>>Intern ID (Desc)</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary btn-filter">Filter Records</button>
            </div>
        </form>
    </div>

    <div class="stat-card table-card">
        <table class="data-table">
            <thead>
                <tr class="table-head-row">
                    <th class="table-th">Intern ID</th>
                    <th class="table-th">Name</th>
                    <th class="table-th">Company</th>
                    <th class="table-th">Status</th>
                    <th class="table-th">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="table-row">
                        <td class="table-td td-bold-dark" style="font-family: monospace;"><?php echo htmlspecialchars($row['internship_id']); ?></td>
                        <td class="table-td td-bold"><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td class="table-td"><?php echo htmlspecialchars($row['company_name'] ?? 'Pending Placement'); ?></td>
                        <td class="table-td">
                            <?php if ($row['is_done']): ?>
                                <span class="badge-status" style="background: #dcfce7; color: #166534; padding: 5px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; border: 1px solid #bbf7d0;">EVALUATED</span>
                            <?php else: ?>
                                <span class="badge-status" style="background: #ffedd5; color: #9a3412; padding: 5px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; border: 1px solid #fed7aa;">PENDING</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-td">
                            <?php if ($row['company_name'] && $row['company_name'] !== 'Pending Placement'): ?>
                                <a href="evaluate_supervisor.php?id=<?php echo urlencode($row['internship_id']); ?>" class="action-link <?php echo $row['is_done'] ? 'action-edit' : 'action-primary'; ?>">
                                    <?php echo $row['is_done'] ? 'RE-EVALUATE' : 'EVALUATE'; ?>
                                </a>
                            <?php else: ?>
                                <span style="color:#9ca3af; font-size:0.85rem; font-style:italic;">Not Yet Placed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="padding: 60px; text-align: center; color: var(--text-muted);">No assigned interns found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="pagination-container" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background: #f8fafc; border-top: 1px solid var(--border-color);">
            <span class="pagination-info" style="font-size: 0.8rem; color: var(--text-muted);">Page <strong><?php echo $page; ?></strong> of <?php echo $total_pages; ?></span>
            <div class="pagination-controls" style="display: flex; gap: 8px;">
                <a href="?<?php echo $query_string; ?>&page=<?php echo $page - 1; ?>" class="page-btn" style="padding: 6px 14px; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: #1e293b; background: white; font-size: 0.8rem; <?php echo ($page <= 1) ? 'opacity: 0.5; pointer-events: none;' : ''; ?>">Prev</a>
                <a href="?<?php echo $query_string; ?>&page=<?php echo $page + 1; ?>" class="page-btn" style="padding: 6px 14px; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: #1e293b; background: white; font-size: 0.8rem; <?php echo ($page >= $total_pages) ? 'opacity: 0.5; pointer-events: none;' : ''; ?>">Next</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include("footer.php"); ?>