<?php
// 1. Start Session & Database Connection
session_start();
require("database.php");

// 2. Security Check: Only Admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

// ==========================================
// NEW: INLINE DELETION LOGIC
// ==========================================
if (isset($_GET['delete_id'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // Fetch the name for the notification banner
    $name_query = "SELECT student_name FROM students WHERE student_id = '$del_id'";
    $name_result = mysqli_query($conn, $name_query);
    
    if (mysqli_num_rows($name_result) > 0) {
        $student_name = mysqli_fetch_assoc($name_result)['student_name'];
        
        // Delete from all tables
        mysqli_begin_transaction($conn);
        try {
            mysqli_query($conn, "DELETE FROM internships WHERE student_id = '$del_id'");
            mysqli_query($conn, "DELETE FROM students WHERE student_id = '$del_id'");
            mysqli_query($conn, "DELETE FROM users WHERE user_id = '$del_id'");
            mysqli_commit($conn);
            
            // Redirect back to this exact page to show the green banner
            $encoded_name = urlencode($student_name);
            header("Location: view_students.php?msg=deleted&name=$encoded_name");
            exit();
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            header("Location: view_students.php?error=delete_failed");
            exit();
        }
    }
}
// ==========================================

// 3. Search & Filter Logic
$search     = isset($_GET['search'])    ? mysqli_real_escape_string($conn, $_GET['search'])    : "";
$filter_prog = isset($_GET['programme']) ? mysqli_real_escape_string($conn, $_GET['programme']) : "";
$filter_company = isset($_GET['company_filter']) ? mysqli_real_escape_string($conn, $_GET['company_filter']) : "";
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Build the base WHERE clause
$where_clause = " WHERE (s.student_id LIKE '%$search%' OR s.student_name LIKE '%$search%')";
if ($filter_prog !== "") {
    $where_clause .= " AND s.programme_id = '$filter_prog'";
}
if ($filter_company === "pending") {
    $where_clause .= " AND i.company_name IS NULL";
} elseif ($filter_company !== "") {
    $where_clause .= " AND i.company_name = '$filter_company'";
}

// Sort order
$order_clause = match($sort) {
    'name_desc' => "s.student_name DESC",
    'id_asc'    => "s.student_id ASC",
    'id_desc'   => "s.student_id DESC",
    'company'   => "i.company_name ASC",
    default     => "s.student_name ASC",
};

// --- PAGINATION LOGIC ---
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query = "SELECT COUNT(*) AS total FROM students s LEFT JOIN internships i ON s.student_id = i.student_id" . $where_clause;
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Main query
$query = "SELECT s.student_id, s.student_name, p.programme_name, i.company_name, i.internship_status
          FROM students s
          LEFT JOIN programmes p ON s.programme_id = p.programme_id
          LEFT JOIN internships i ON s.student_id = i.student_id"
          . $where_clause .
          " ORDER BY $order_clause
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);

// Fetch programmes for dropdown
$prog_result = mysqli_query($conn, "SELECT programme_id, programme_name FROM programmes ORDER BY programme_name ASC");

// Fetch distinct companies for dropdown
$company_result = mysqli_query($conn, "SELECT DISTINCT company_name FROM internships WHERE company_name IS NOT NULL ORDER BY company_name ASC");

$query_string = http_build_query(['search' => $search, 'programme' => $filter_prog, 'company_filter' => $filter_company, 'sort' => $sort]);

include("header.php");
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
/* Clean up the Select2 styling to match your dashboard filter bar */
.filter-form-compact .select2-container {
    flex: 1;
    min-width: 150px;
}
.select2-container .select2-selection--single {
    height: 42px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    display: flex;
    align-items: center;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #1f2937;
    font-size: 0.95rem;
}
</style>

<div class="page-header-flex">
    <div>
        <span class="stat-label">Registry Database</span>
        <h1 class="page-title-main">Student Records</h1>
        <a href="admin_dashboard.php" class="back-link">
            &larr; Back to Dashboard
        </a>
    </div>
    <a href="add_student.php" class="btn-primary">+ Register New Student</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'deleted'): ?>
        <div class="edit-notice success" style="margin-bottom: 30px;">
            <span class="notice-icon">✓</span>
            <div>
               Record <strong><?php echo htmlspecialchars(urldecode($_GET['name'] ?? 'the student')); ?></strong> successfully deleted.
            </div>
        </div>
    <?php elseif ($_GET['msg'] === 'added'): ?>
        <div class="edit-notice success" style="margin-bottom: 30px;">
            <span class="notice-icon">✓</span>
            <div>
                New student <strong><?php echo htmlspecialchars(urldecode($_GET['name'] ?? '')); ?></strong> successfully registered.
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="stat-card filter-form-card">
    <form method="GET" action="view_students.php" class="filter-form filter-form-compact">

        <div class="filter-form-group">
            <input type="text" name="search" placeholder="Search by ID or Name..." class="filter-input" value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <div class="filter-select-group" style="flex: 1;">
            <select name="programme" class="search-select">
                <option value="">All Programmes</option>
                <?php while($prog = mysqli_fetch_assoc($prog_result)): ?>
                    <option value="<?php echo htmlspecialchars($prog['programme_id']); ?>" <?php if($filter_prog == $prog['programme_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($prog['programme_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="filter-select-group" style="flex: 1;">
            <select name="company_filter" class="search-select">
                <option value="">All Companies</option>
                <option value="pending" <?php echo ($filter_company === 'pending') ? 'selected' : ''; ?>>Pending Placement</option>
                <?php while($comp = mysqli_fetch_assoc($company_result)): ?>
                    <option value="<?php echo htmlspecialchars($comp['company_name']); ?>" <?php echo ($filter_company === $comp['company_name']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($comp['company_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="filter-select-group" style="flex: 1;">
            <select name="sort" class="search-select">
                <option value="name_asc"  <?php echo ($sort === 'name_asc')  ? 'selected' : ''; ?>>Name (A → Z)</option>
                <option value="name_desc" <?php echo ($sort === 'name_desc') ? 'selected' : ''; ?>>Name (Z → A)</option>
                <option value="id_asc"   <?php echo ($sort === 'id_asc')    ? 'selected' : ''; ?>>Student ID (Asc)</option>
                <option value="id_desc"  <?php echo ($sort === 'id_desc')   ? 'selected' : ''; ?>>Student ID (Desc)</option>
                <option value="company"  <?php echo ($sort === 'company')   ? 'selected' : ''; ?>>Company (A → Z)</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary btn-filter">Filter Students</button>
            <?php if($search !== "" || $filter_prog !== "" || $filter_company !== "" || $sort !== 'name_asc'): ?>
                <a href="view_students.php" class="clear-link">Clear All</a>
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
                <th class="table-th">Actions</th>
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
                    <td class="table-td">
                        <a href="edit_student.php?id=<?php echo urlencode($row['student_id']); ?>" class="action-link action-edit">EDIT</a>
                        
                        <a href="view_students.php?delete_id=<?php echo urlencode($row['student_id']); ?>" class="action-link action-delete" 
                           onclick="return confirm('Are you sure you want to delete the record for <?php echo htmlspecialchars(addslashes($row['student_name'])); ?>? This action cannot be undone.');">
                           DELETE
                        </a>

                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="empty-state-td">No student records found matching your criteria.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
    <div class="pagination-container">
        <span class="pagination-info">Showing Page <?php echo $page; ?> of <?php echo $total_pages; ?> (<?php echo $total_rows; ?> Total Records)</span>

        <div class="pagination-controls">
            <a href="?<?php echo $query_string; ?>&page=<?php echo $page - 1; ?>"
               class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
               Previous
            </a>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?<?php echo $query_string; ?>&page=<?php echo $i; ?>"
                   class="page-btn <?php echo ($i == $page) ? 'active' : ''; ?>">
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <a href="?<?php echo $query_string; ?>&page=<?php echo $page + 1; ?>"
               class="page-btn <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
               Next
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include("footer.php"); ?>