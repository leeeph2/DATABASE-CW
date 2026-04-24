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

// Company filter
$filter_company = isset($_GET['company_filter']) ? mysqli_real_escape_string($conn, $_GET['company_filter']) : "";
if ($filter_company === "pending") {
    $where_clause .= " AND i.company_name IS NULL";
} elseif ($filter_company !== "") {
    $where_clause .= " AND i.company_name = '$filter_company'";
}

// Sort order
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
$order_clause = match($sort) {
    'name_desc' => "s.student_name DESC",
    'id_asc'    => "s.student_id ASC",
    'id_desc'   => "s.student_id DESC",
    'company'   => "i.company_name ASC",
    default     => "s.student_name ASC",
};

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
          " ORDER BY $order_clause
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Fetch programmes for dropdown
$prog_result = mysqli_query($conn, "SELECT programme_id, programme_name FROM programmes ORDER BY programme_name ASC");

// FIX: Fetch companies for dropdown (was missing — caused $company_result to be undefined)
$company_result = mysqli_query($conn, "SELECT DISTINCT company_name FROM internships WHERE company_name IS NOT NULL ORDER BY company_name ASC");

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
    <form method="GET" action="view_my_students.php" class="filter-form filter-form-compact">

        <?php if ($is_evaluate_mode): ?>
            <input type="hidden" name="action" value="evaluate">
        <?php endif; ?>

        <div class="filter-form-group">
            <input type="text" name="search" placeholder="Search by ID or Name..." class="filter-input" value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <div class="filter-select-group">
            <select name="programme" class="filter-select">
                <option value="">All Programmes</option>
                <?php while($prog = mysqli_fetch_assoc($prog_result)): ?>
                    <option value="<?php echo htmlspecialchars($prog['programme_id']); ?>" <?php if($filter_prog == $prog['programme_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($prog['programme_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="filter-select-group">
            <select name="company_filter" class="filter-select">
                <option value="">All Companies</option>
                <option value="pending" <?php echo ($filter_company === 'pending') ? 'selected' : ''; ?>>Pending Placement</option>
                <?php while($comp = mysqli_fetch_assoc($company_result)): ?>
                    <option value="<?php echo htmlspecialchars($comp['company_name']); ?>" <?php echo ($filter_company === $comp['company_name']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($comp['company_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="filter-select-group">
            <select name="sort" class="filter-select">
                <option value="name_asc"  <?php echo ($sort === 'name_asc')  ? 'selected' : ''; ?>>Name (A → Z)</option>
                <option value="name_desc" <?php echo ($sort === 'name_desc') ? 'selected' : ''; ?>>Name (Z → A)</option>
                <option value="id_asc"   <?php echo ($sort === 'id_asc')    ? 'selected' : ''; ?>>Student ID (Asc)</option>
                <option value="id_desc"  <?php echo ($sort === 'id_desc')   ? 'selected' : ''; ?>>Student ID (Desc)</option>
                <option value="company"  <?php echo ($sort === 'company')   ? 'selected' : ''; ?>>Company (A → Z)</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary btn-filter">Filter Records</button>
            <?php if($search !== "" || $filter_prog !== "" || $filter_company !== "" || $sort !== 'name_asc'): ?>
                <a href="view_my_students.php<?php echo $is_evaluate_mode ? '?action=evaluate' : ''; ?>" class="clear-link">Clear All</a>
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