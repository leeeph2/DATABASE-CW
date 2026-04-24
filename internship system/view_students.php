<?php
// 1. Start Session & Database Connection
session_start();
require("database.php");

// 2. Security Check: Only Admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

// 3. Search & Filter Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$filter_prog = isset($_GET['programme']) ? mysqli_real_escape_string($conn, $_GET['programme']) : "";

// Build the base WHERE clause (used for both data and pagination counting)
$where_clause = " WHERE (s.student_id LIKE '%$search%' OR s.student_name LIKE '%$search%')";
if ($filter_prog !== "") {
    $where_clause .= " AND s.programme_id = '$filter_prog'";
}

// --- PAGINATION LOGIC ---
$limit = 10; // Number of students per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total records for pagination math
$count_query = "SELECT COUNT(*) AS total FROM students s" . $where_clause;
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);
// ------------------------

// Main query with LIMIT and OFFSET applied
$query = "SELECT s.student_id, s.student_name, p.programme_name, u.full_name AS supervisor_name, i.company_name, i.internship_status
          FROM students s
          LEFT JOIN programmes p ON s.programme_id = p.programme_id
          LEFT JOIN users u ON s.supervisor_id = u.user_id
          LEFT JOIN internships i ON s.student_id = i.student_id" 
          . $where_clause . 
          " ORDER BY s.student_name ASC 
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);

// Fetch programmes for the dropdown filter
$prog_result = mysqli_query($conn, "SELECT programme_id, programme_name FROM programmes ORDER BY programme_name ASC");

// Helper variable to preserve search queries across pagination links
$query_string = http_build_query(['search' => $search, 'programme' => $filter_prog]);

// 5. Load the Global Academic Header
include("header.php"); 
?>

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

<div class="stat-card filter-form-card">
    <form method="GET" action="view_students.php" class="filter-form">
        
        <div class="filter-form-group">
            <input type="text" name="search" placeholder="Search by Student ID or Name..." class="filter-input" value="<?php echo htmlspecialchars($search); ?>">
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
            <a href="view_students.php" class="clear-link">Clear All</a>
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
                        <a href="delete_student.php?id=<?php echo urlencode($row['student_id']); ?>" class="action-link action-delete" onclick="return confirm('Are you sure you want to delete this student record? This action cannot be undone.');">DELETE</a>
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