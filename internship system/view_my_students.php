<?php
// 1. Start Session & Database Connection
session_start();
require("database.php");

// 2. Security Check: Only allow Assessors
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assessor') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$lecturer_id = $_SESSION['user_id'];

// Check if the user is in "Evaluate" mode (triggered from the dashboard card)
$is_evaluate_mode = isset($_GET['action']) && $_GET['action'] === 'evaluate';

// 3. Search & Filter Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$filter_prog = isset($_GET['programme']) ? mysqli_real_escape_string($conn, $_GET['programme']) : "";

// Build the base WHERE clause
$where_clause = " WHERE s.supervisor_id = '$lecturer_id'";
if ($search !== "") {
    $where_clause .= " AND (s.student_id LIKE '%$search%' OR s.student_name LIKE '%$search%')";
}
if ($filter_prog !== "") {
    $where_clause .= " AND s.programme_id = '$filter_prog'";
}

// 4. Pagination Logic
$limit = 10; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total records for pagination math
$count_query = "SELECT COUNT(*) as total FROM students s" . $where_clause;
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// 5. Fetch Data for Current Page
$query = "SELECT s.student_id, s.student_name, p.programme_name, i.company_name
          FROM students s
          LEFT JOIN programmes p ON s.programme_id = p.programme_id
          LEFT JOIN internships i ON s.student_id = i.student_id"
          . $where_clause . 
          " ORDER BY s.student_name ASC
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Fetch programmes for dropdown
$prog_result = mysqli_query($conn, "SELECT programme_id, programme_name FROM programmes ORDER BY programme_name ASC");

// Helper function to build pagination URLs while preserving current filters and modes
$url_params = $_GET;
function build_url($page_num, $params) {
    $params['page'] = $page_num;
    return '?' . http_build_query($params);
}

include("header.php"); 
?>

<div class="page-header-flex">
    <div>
        <span class="stat-label">Registry Database</span>
        <h1 class="page-title-main">
            <?php echo $is_evaluate_mode ? 'Select Student to Evaluate' : 'Assigned Student Roster'; ?>
        </h1>
        <a href="assessor_dashboard.php" class="back-link">
            &larr; Back to Dashboard
        </a>
    </div>
</div>

<div class="stat-card filter-form-card">
    <form method="GET" action="view_my_students.php" class="filter-form">
        <?php if ($is_evaluate_mode): ?>
            <input type="hidden" name="action" value="evaluate">
        <?php endif; ?>

        <div class="filter-form-group">
            <input type="text" name="search" class="filter-input" placeholder="Search by ID or Name..." value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <div class="filter-select-group">
            <select name="programme" class="filter-select">
                <option value="">All Academic Programmes</option>
                <?php while($prog = mysqli_fetch_assoc($prog_result)): ?>
                    <option value="<?php echo htmlspecialchars($prog['programme_id']); ?>" <?php if($filter_prog == $prog['programme_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($prog['programme_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn-primary">Filter Records</button>
        
        <?php if($search !== "" || $filter_prog !== ""): ?>
            <a href="view_my_students.php<?php echo $is_evaluate_mode ? '?action=evaluate' : ''; ?>" class="clear-link">Clear All</a>
        <?php endif; ?>
    </form>
</div>

<div class="stat-card table-card">
    <table class="data-table">
        <thead>
            <tr class="table-head-row">
                <th class="table-th">Student ID</th>
                <th class="table-th">Name</th>
                <th class="table-th">Programme</th>
                <th class="table-th">Company</th>
                <?php if ($is_evaluate_mode): ?>
                    <th class="table-th">Action</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr class="table-row">
                    <td class="table-td td-bold-dark"><?php echo htmlspecialchars($row['student_id']); ?></td>
                    <td class="table-td td-bold"><?php echo htmlspecialchars($row['student_name']); ?></td>
                    <td class="table-td"><?php echo htmlspecialchars($row['programme_name'] ?? 'Unassigned'); ?></td>
                    <td class="table-td"><?php echo htmlspecialchars($row['company_name'] ?? 'Pending Placement'); ?></td>
                    
                    <?php if ($is_evaluate_mode): ?>
                    <td class="table-td">
                        <a href="evaluate_student.php?id=<?php echo urlencode($row['student_id']); ?>" class="action-link action-edit">EVALUATE</a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo $is_evaluate_mode ? '5' : '4'; ?>" class="empty-state-td">
                        No student records found matching your criteria.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
    <div class="pagination-container">
        <span class="pagination-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?> (<?php echo $total_records; ?> Total)</span>
        
        <div class="pagination-controls">
            <a href="<?php echo build_url($page - 1, $url_params); ?>" 
               class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
               Previous
            </a>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="<?php echo build_url($i, $url_params); ?>" 
                   class="page-btn <?php echo ($i === $page) ? 'active' : ''; ?>">
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <a href="<?php echo build_url($page + 1, $url_params); ?>" 
               class="page-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
               Next
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include("footer.php"); ?>