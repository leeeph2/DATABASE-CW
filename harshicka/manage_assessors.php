<?php
// 1. Start Session & Database Connection
session_start();
require("database.php");

// 2. Security Check: Only Admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$message = "";

// 3. DELETE FUNCTIONALITY
if (isset($_GET['delete'])) {
    $target_id = mysqli_real_escape_string($conn, $_GET['delete']);

    // Safety check: Don't let the Admin delete themselves
    if ($target_id === $_SESSION['user_id']) {
        $message = "<div class='error-notification' style='padding: 14px; background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; border-radius: 8px; text-align: center; margin-bottom: 20px;'>You cannot delete your own admin account!</div>";
    } else {
      // Fetch the name AND role BEFORE deleting
        $name_query = mysqli_query($conn, "SELECT full_name, role FROM users WHERE user_id = '$target_id'");
        $user_data  = mysqli_fetch_assoc($name_query);
        $deleted_name = $user_data['full_name'] ?? "Assessor";
        $deleted_role = $user_data['role'] ?? "Staff member";

        try {
            if (mysqli_query($conn, "DELETE FROM users WHERE user_id = '$target_id'")) {
                $encoded_name = urlencode($deleted_name);
                header("Location: manage_assessors.php?msg=removed&name=$encoded_name");
                exit();
            }
        } catch (mysqli_sql_exception $e) {
            // Error code 1451 specifically means a Foreign Key constraint failed
            if ($e->getCode() == 1451) {
                $safe_name = htmlspecialchars($deleted_name);
                $message = "<div class='error-notification' style='padding: 14px; background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; border-radius: 8px; text-align: center; margin-bottom: 20px;'>
                                <strong>Cannot Delete:</strong> $deleted_role <strong>$safe_name</strong> has already submitted student assessments. Deleting them would corrupt existing academic records.
                            </div>";
            } else {
                // Catch any other weird database errors
                $message = "<div class='error-notification' style='padding: 14px; background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; border-radius: 8px; text-align: center; margin-bottom: 20px;'>
                                Database error: " . htmlspecialchars($e->getMessage()) . "
                            </div>";
            }
        }
    }
}

// 4. Search & Filter Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Build the base WHERE clause
$where_clause = " WHERE (full_name LIKE '%$search%' OR user_id LIKE '%$search%' OR username LIKE '%$search%') AND role IN ('Admin', 'Lecturer', 'Supervisor')";

// Sort order
$order_clause = match($sort) {
    'name_desc' => "full_name DESC",
    'id_asc'    => "user_id ASC",
    'id_desc'   => "user_id DESC",
    'role'      => "role ASC, full_name ASC",
    default     => "full_name ASC",
};

// 5. PAGINATION LOGIC
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$count_query = "SELECT COUNT(*) AS total FROM users" . $where_clause;
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Main Query with Limits
$sql = "SELECT * FROM users " . $where_clause . " ORDER BY $order_clause LIMIT $limit OFFSET $offset";
$res = mysqli_query($conn, $sql);

// Build query string for pagination links
$query_string = http_build_query(['search' => $search, 'sort' => $sort]);

// 6. Load the Global Academic Header
include("header.php");
?>

<div class="page-header-flex">
    <div>
        <span class="stat-label">Faculty Management</span>
        <h1 class="page-title-main">Staff Records</h1>
        <a href="admin_dashboard.php" class="back-link">
            &larr; Back to Dashboard
        </a>
    </div>
    <a href="add_staff.php" class="btn-primary">+ Register New Staff</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'registered'): ?>
        <div class="edit-notice success" style="margin-bottom: 30px;">
            <span class="notice-icon">✓</span>
            <div>
                New staff member <strong><?php echo htmlspecialchars(urldecode($_GET['name'] ?? '')); ?></strong> registered successfully.
            </div>
        </div>
    <?php elseif ($_GET['msg'] === 'removed'): ?>
        <div class="edit-notice success" style="margin-bottom: 30px;">
            <span class="notice-icon">✓</span>
            <div>
                Record for <strong><?php echo htmlspecialchars(urldecode($_GET['name'] ?? 'the staff member')); ?></strong> has been successfully deleted.
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($message): ?>
    <div style="margin-bottom: 30px;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="stat-card filter-form-card">
    <form method="GET" action="manage_assessors.php" class="filter-form filter-form-compact">

        <div class="filter-form-group">
            <input type="text" name="search" placeholder="Search by ID, fullname or username..."
                   class="filter-input" value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <div class="filter-select-group">
            <select name="sort" class="filter-select">
                <option value="name_asc"  <?php echo ($sort === 'name_asc')  ? 'selected' : ''; ?>>Name (A → Z)</option>
                <option value="name_desc" <?php echo ($sort === 'name_desc') ? 'selected' : ''; ?>>Name (Z → A)</option>
                <option value="id_asc"   <?php echo ($sort === 'id_asc')    ? 'selected' : ''; ?>>Staff ID (Asc)</option>
                <option value="id_desc"  <?php echo ($sort === 'id_desc')   ? 'selected' : ''; ?>>Staff ID (Desc)</option>
                <option value="role"     <?php echo ($sort === 'role')      ? 'selected' : ''; ?>>Role (Admin / Lecturer / Supervisor)</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary btn-filter">Search Staff</button>
            <?php if ($search !== "" || $sort !== 'name_asc'): ?>
                <a href="manage_assessors.php" class="clear-link">Clear All</a>
            <?php endif; ?>
        </div>

    </form>
</div>

<div class="stat-card table-card">
    <table class="data-table">
        <thead>
            <tr class="table-head-row">
                <th class="table-th">Staff ID</th>
                <th class="table-th">Full Name</th>
                <th class="table-th">Username</th>
                <th class="table-th">Role</th>
                <th class="table-th">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($res) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($res)): ?>
                <tr class="table-row">
                    <td class="table-td td-bold-dark"><?php echo htmlspecialchars($row['user_id']); ?></td>
                    <td class="table-td td-bold"><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td class="table-td"><?php echo htmlspecialchars($row['username']); ?></td>
                    <td class="table-td">
                        <?php if ($row['role'] === 'Admin'): ?>
                            <span style= padding: 4px 10px; border-radius: 4px; font-size: 0.95rem; font-weight: 700;">
                                Admin
                            </span>
                        <?php elseif ($row['role'] === 'Lecturer'): ?>
                            <span style=padding: 4px 10px; border-radius: 4px; font-size: 0.95rem; font-weight: 700;">
                                Lecturer
                            </span>
                        <?php elseif ($row['role'] === 'Supervisor'): ?>
                            <span style=padding: 4px 10px; border-radius: 4px; font-size: 0.95rem; font-weight: 700;">
                                Supervisor
                            </span>
                        <?php else: ?>
                            <?php echo htmlspecialchars($row['role']); ?>
                        <?php endif; ?>
                    </td>
                    <td class="table-td">
                        <?php if ($row['user_id'] !== $_SESSION['user_id']): ?>
                            <a href="edit_staff.php?id=<?php echo urlencode($row['user_id']); ?>" class="action-link action-edit">EDIT</a>
                            
                            <a href="manage_assessors.php?delete=<?php echo urlencode($row['user_id']); ?>"
                               class="action-link action-delete"
                               onclick="return confirm('Are you sure you want to delete the account for <?php echo htmlspecialchars(addslashes($row['full_name'])); ?>? This will unassign them from all current students.');">
                               DELETE
                            </a>
                        <?php else: ?>
                            <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">ACTIVE (YOU)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="empty-state-td">No staff members found matching your criteria.</td>
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