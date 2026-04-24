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

// 3. Search Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";

// Build base WHERE clause
$where_clause = " WHERE s.supervisor_id = '$lecturer_id' 
                  AND (s.student_id LIKE '%$search%' OR s.student_name LIKE '%$search%')";

// --- PAGINATION LOGIC ---
$limit = 10; // Students per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total records for math
$count_query = "SELECT COUNT(*) as total FROM students s" . $where_clause;
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);
// ------------------------

// 4. Fetch Data with Limit/Offset
$query = "SELECT s.student_id, s.student_name, i.company_name 
          FROM students s 
          LEFT JOIN internships i ON s.student_id = i.student_id " 
          . $where_clause . 
          " ORDER BY s.student_name ASC
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Preserve search query for pagination links
$query_string = http_build_query(['search' => $search]);

include("header.php");
?>

<div class="page-header-flex">
    <div>
        <span class="stat-label">Evaluation Portal</span>
        <h1 class="page-title-main">Select Student to Evaluate</h1>
        <a href="assessor_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
    </div>
</div>

<div class="stat-card filter-form-card">
    <form method="GET" action="evaluate_list.php" class="filter-form">
        <div class="filter-form-group">
            <input type="text" name="search" class="filter-input" placeholder="Search by Student ID or Name..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <button type="submit" class="btn-primary">Search Records</button>
        
        <?php if($search !== ""): ?>
            <a href="evaluate_list.php" class="clear-link">Clear Search</a>
        <?php endif; ?>
    </form>
</div>

<div class="stat-card table-card">
    <table class="data-table">
        <thead>
            <tr class="table-head-row">
                <th class="table-th">Student ID</th>
                <th class="table-th">Name</th>
                <th class="table-th">Company</th>
                <th class="table-th">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr class="table-row">
                    <td class="table-td td-bold-dark"><?php echo htmlspecialchars($row['student_id']); ?></td>
                    <td class="table-td td-bold"><?php echo htmlspecialchars($row['student_name']); ?></td>
                    <td class="table-td"><?php echo htmlspecialchars($row['company_name'] ?? 'Pending Placement'); ?></td>
                    <td class="table-td">
                        <a href="evaluate_student.php?id=<?php echo urlencode($row['student_id']); ?>" class="action-link action-edit">
                            EVALUATE
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="empty-state-td">No students assigned to you found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
    <div class="pagination-container">
        <span class="pagination-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?> (<?php echo $total_records; ?> Total)</span>
        
        <div class="pagination-controls">
            <a href="?<?php echo $query_string; ?>&page=<?php echo $page - 1; ?>" 
               class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
               Previous
            </a>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?<?php echo $query_string; ?>&page=<?php echo $i; ?>" 
                   class="page-btn <?php echo ($i === $page) ? 'active' : ''; ?>">
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