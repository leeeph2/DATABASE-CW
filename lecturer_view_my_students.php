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
$dash_link = ($_SESSION['role'] === 'Supervisor') ? 'supervisor_dashboard.php' : 'lecturer_dashboard.php';

// 3. Search & Filter Logic (FIXED GLOBALLY)
$search        = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$filter_status = $_GET['status'] ?? "";

$where_clause = " WHERE (i.lecturer_id = '$lecturer_id' OR i.supervisor_id = '$lecturer_id')";

if ($search !== "") {
    $where_clause .= " AND (s.student_id LIKE '%$search%' OR s.student_name LIKE '%$search%')";
}

if ($filter_status === "done") {
    $where_clause .= " AND (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) >= 2";
} elseif ($filter_status === "pending") {
    $where_clause .= " AND (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) = 1";
} elseif ($filter_status === "not_started") {
    $where_clause .= " AND (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) = 0";
}

// 4. Pagination Logic
$limit         = 10;
$page          = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset        = ($page - 1) * $limit;

$count_query   = "SELECT COUNT(DISTINCT s.student_id) as total 
                  FROM students s 
                  INNER JOIN internships i ON s.student_id = i.student_id"
                  . $where_clause;
$total_records = mysqli_fetch_assoc(mysqli_query($conn, $count_query))['total'];
$total_pages   = ceil($total_records / $limit);

// 5. Main Data Query (FIXED to count total evaluations)
$query = "SELECT 
            s.student_id, 
            s.student_name, 
            i.company_name, 
            i.internship_id,
            (SELECT AVG(total_mark) FROM assessments WHERE internship_id = i.internship_id AND assessment_type = 'Academic') as avg_marks,
            (SELECT COUNT(*) FROM assessments WHERE internship_id = i.internship_id) as total_evals
          FROM students s 
          INNER JOIN internships i ON s.student_id = i.student_id"
          . $where_clause .
          " ORDER BY s.student_name ASC 
            LIMIT $limit OFFSET $offset";

$result       = mysqli_query($conn, $query);
$query_string = http_build_query(['search' => $search, 'status' => $filter_status]);

include("header.php");
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="dashboard-container">

    <div class="page-header-flex">
        <div>
            <span class="stat-label">Student Directory</span>
            <h1 class="page-title-main">View Student Progression</h1>
            <a href="<?php echo $dash_link; ?>" class="back-link">&larr; Back to Dashboard</a>
        </div>
    </div>

    <div class="stat-card filter-form-card">
    <form method="GET" action="lecturer_view_my_students.php" class="filter-form filter-form-compact">

        <div class="filter-form-group">
            <input type="text" name="search" placeholder="Search by ID or Name..." class="filter-input" value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <div class="filter-select-group" style="flex: 1;">
            <select name="status" class="search-select">
                <option value="">All Status</option>
                <option value="done"        <?php echo ($filter_status == 'done')        ? 'selected' : ''; ?>>Completed (2/2)</option>
                <option value="pending"     <?php echo ($filter_status == 'pending')     ? 'selected' : ''; ?>>Pending (1/2)</option>
                <option value="not_started" <?php echo ($filter_status == 'not_started') ? 'selected' : ''; ?>>Not Started</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary btn-filter">Filter Students</button>
            <?php if ($search !== "" || $filter_status !== ""): ?>
                <a href="lecturer_view_my_students.php" class="clear-link">Clear All</a>
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
                    <th class="table-th">Completion</th>
                    <th class="table-th">Academic Grade</th>
                    <th class="table-th">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="table-row">
                        <td class="table-td td-bold-dark"><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td class="table-td td-bold"><?php echo htmlspecialchars($row['student_name']); ?></td>
                       <td class="table-td">
    <?php if ($row['total_evals'] >= 2): ?>
        <span class="badge-completed">&#10003; COMPLETED (2/2)</span>
    <?php elseif ($row['total_evals'] == 1): ?>
        PENDING (1/2)
    <?php else: ?>
        NOT STARTED
    <?php endif; ?>
</td>
                        <td class="table-td td-bold">
                            <?php if ($row['total_evals'] >= 2): ?>
                                <?php echo number_format($row['avg_marks'], 1); ?>%
                            <?php else: ?>
                                <span style="color:#cbd5e1;">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-td">
                            <?php if ($row['total_evals'] >= 2): ?>
                                <a href="view_final_report.php?id=<?php echo $row['internship_id']; ?>"
                                   class="action-link action-edit">VIEW REPORT</a>
                            <?php else: ?>
                                <span class="table-td" style="font-style: italic;">Locked</span>
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