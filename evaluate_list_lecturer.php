<?php
// 1. Start Session & Database Connection
session_start();
require("database.php");

// 2. Security Check: Only allow Assessors (Lecturer/Supervisor)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Lecturer' && $_SESSION['role'] !== 'Supervisor')) {
    header("Location: index.php?error=unauthorized");
    exit();
}

$lecturer_id = $_SESSION['user_id'];
$dash_link = 'lecturer_dashboard.php';

// 3. Search & Filter Logic (FIXED GLOBALLY)
$search         = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$status_filter  = $_GET['status_filter'] ?? "";
$sort           = $_GET['sort'] ?? "name_asc";

$where_clause = " WHERE (i.lecturer_id = '$lecturer_id' OR i.supervisor_id = '$lecturer_id')";

if ($search !== "") {
    $where_clause .= " AND (s.student_id LIKE '%$search%' OR s.student_name LIKE '%$search%')";
}

if ($status_filter === "completed") {
    $where_clause .= " AND (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) >= 2";
} elseif ($status_filter === "pending_half") {
    $where_clause .= " AND (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) = 1";
} elseif ($status_filter === "not_started") {
    $where_clause .= " AND (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) = 0";
}

$order_by = match($sort) {
    'name_desc' => "s.student_name DESC",
    'id_asc'    => "s.student_id ASC",
    'id_desc'   => "s.student_id DESC",
    default     => "s.student_name ASC",
};

// 4. Pagination
$limit  = 10;
$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query   = "SELECT COUNT(DISTINCT s.student_id) as total FROM students s 
                  INNER JOIN internships i ON s.student_id = i.student_id" . $where_clause;
$total_records = mysqli_fetch_assoc(mysqli_query($conn, $count_query))['total'] ?? 0;
$total_pages   = ceil($total_records / $limit);

// 5. Main Data Query (FIXED to count total evaluations AND personal evaluations)
$query = "SELECT 
              s.student_id, s.student_name, i.company_name, i.internship_id,
              (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) AS total_evals,
              (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id AND assessment_type = 'Academic') AS my_eval_count
          FROM students s
          INNER JOIN internships i ON s.student_id = i.student_id "
          . $where_clause . " ORDER BY $order_by LIMIT $limit OFFSET $offset";

$result       = mysqli_query($conn, $query);
$query_string = http_build_query(['search' => $search, 'status_filter' => $status_filter, 'sort' => $sort]);

include("header.php");
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="dashboard-container">

    <div class="page-header-flex">
        <div>
            <span class="stat-label">Evaluation Portal</span>
            <h1 class="page-title-main">Select Student to Evaluate</h1>
            <a href="<?php echo $dash_link; ?>" class="back-link">&larr; Back to Dashboard</a>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'evaluated'): ?>
        <div class="edit-notice success" style="margin-bottom: 30px;">
            <span class="notice-icon">✓</span>
            <div>
                Student <strong><?php echo htmlspecialchars(urldecode($_GET['name'] ?? '')); ?></strong> has been evaluated successfully.
            </div>
        </div>
    <?php endif; ?>

    <div class="stat-card filter-form-card">
        <form method="GET" action="evaluate_list_lecturer.php" class="filter-form filter-form-compact">

            <div class="filter-form-group">
                <input type="text" name="search" class="filter-input" placeholder="Search by ID or Name..." value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="filter-select-group" style="flex: 1;">
                <select name="status_filter" class="search-select">
                    <option value="">All Status</option>
                    <option value="completed"    <?php echo ($status_filter == 'completed')    ? 'selected' : ''; ?>>Completed (2/2)</option>
                    <option value="pending_half" <?php echo ($status_filter == 'pending_half') ? 'selected' : ''; ?>>Pending (1/2)</option>
                    <option value="not_started"  <?php echo ($status_filter == 'not_started')  ? 'selected' : ''; ?>>Not Started</option>
                </select>
            </div>

            <div class="filter-select-group" style="flex: 1;">
                <select name="sort" class="search-select">
                    <option value="name_asc"  <?php echo ($sort == 'name_asc')  ? 'selected' : ''; ?>>Name (A → Z)</option>
                    <option value="name_desc" <?php echo ($sort == 'name_desc') ? 'selected' : ''; ?>>Name (Z → A)</option>
                    <option value="id_asc"   <?php echo ($sort == 'id_asc')    ? 'selected' : ''; ?>>Student ID (Asc)</option>
                    <option value="id_desc"  <?php echo ($sort == 'id_desc')   ? 'selected' : ''; ?>>Student ID (Desc)</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-primary btn-filter">Filter Students</button>
                <?php if ($search !== "" || $status_filter !== ""): ?>
                    <a href="evaluate_list_lecturer.php" class="clear-link">Clear All</a>
                <?php endif; ?>
            </div>

        </form>
    </div>

    <div class="stat-card table-card">
        <table class="data-table">
            <thead>
                <tr class="table-head-row">
                    <th class="table-th">Student ID</th>
                    <th class="table-th">Name</th>
                    <th class="table-th">Company</th>
                    <th class="table-th">Status</th>
                    <th class="table-th">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="table-row">
                        <td class="table-td td-bold-dark"><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td class="table-td td-bold"><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td class="table-td"><?php echo htmlspecialchars($row['company_name'] ?? 'Pending Placement'); ?></td>
                       <td class="table-td">
    <?php if ($row['total_evals'] >= 2): ?>
    <span style="color: #28a745; font-weight: bold;">
        &#10003; COMPLETED (2/2)
    </span>

<?php elseif ($row['total_evals'] == 1): ?>
    <span style="color: #ff7f50; font-weight: bold;">
        PENDING (1/2)
    </span>

<?php else: ?>
    <span style="color: #6c757d; font-weight: bold;">
        NOT STARTED
    </span>
<?php endif; ?>
</td>
                        <td class="table-td">
                            <?php if ($row['company_name'] && $row['company_name'] !== 'Pending Placement'): ?>
                                <a href="evaluate_lecturer.php?id=<?php echo urlencode($row['student_id']); ?>"
                                   class="action-link <?php echo ($row['my_eval_count'] > 0) ? 'action-reevaluate' : 'action-edit'; ?>">
                                    <?php echo ($row['my_eval_count'] > 0) ? 'RE-EVALUATE' : 'EVALUATE'; ?>
                                </a>
                            <?php else: ?>
                                <span class="table-td" style="font-style: italic;">Not Yet Placed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="empty-state-td">No records found matching your criteria.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <span class="pagination-info">Showing Page <?php echo $page; ?> of <?php echo $total_pages; ?> (<?php echo $total_records; ?> Total Records)</span>
            <div class="pagination-controls">
                <a href="?<?php echo $query_string; ?>&page=<?php echo $page - 1; ?>"
                   class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">Previous</a>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?<?php echo $query_string; ?>&page=<?php echo $i; ?>"
                       class="page-btn <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <a href="?<?php echo $query_string; ?>&page=<?php echo $page + 1; ?>"
                   class="page-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">Next</a>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php include("footer.php"); ?>