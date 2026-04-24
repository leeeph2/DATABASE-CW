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
        // Fetch the name BEFORE deleting
        $name_query = mysqli_query($conn, "SELECT full_name FROM users WHERE user_id = '$target_id'");
        $user_data  = mysqli_fetch_assoc($name_query);
        $deleted_name = $user_data['full_name'] ?? "Assessor";

        // Unassign any students tied to this Assessor first
        mysqli_query($conn, "UPDATE students SET supervisor_id = NULL WHERE supervisor_id = '$target_id'");

        if (mysqli_query($conn, "DELETE FROM users WHERE user_id = '$target_id'")) {
            $encoded_name = urlencode($deleted_name);
            header("Location: view_assessors.php?msg=removed&name=$encoded_name");
            exit();
        } else {
            $message = "<div class='error-notification' style='padding: 14px; background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; border-radius: 8px; text-align: center; margin-bottom: 20px;'>Database error: Could not delete user.</div>";
        }
    }
}

// 4. Search Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";

// 5. Load the Global Academic Header
include("header.php");
?>

<div class="page-header-flex">
    <div>
        <span class="stat-label">Faculty Management</span>
        <h1 class="page-title-main">Assessor Records</h1>
        <a href="admin_dashboard.php" class="back-link">
            &larr; Back to Dashboard
        </a>
    </div>
    <a href="add_assessor.php" class="btn-primary">+ Register New Assessor</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'registered'): ?>
        <div class="edit-notice success" style="margin-bottom: 30px;">
            <span class="notice-icon">✓</span>
            <div>
                New assessor <strong><?php echo htmlspecialchars(urldecode($_GET['name'] ?? '')); ?></strong> registered successfully.
            </div>
        </div>
    <?php elseif ($_GET['msg'] === 'removed'): ?>
        <div class="edit-notice success" style="margin-bottom: 30px;">
            <span class="notice-icon">✓</span>
            <div>
                Record for <strong><?php echo htmlspecialchars(urldecode($_GET['name'] ?? 'the assessor')); ?></strong> has been successfully deleted.
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
    <form method="GET" action="view_assessors.php" class="filter-form filter-form-compact">

        <div class="filter-form-group">
            <input type="text" name="search" placeholder="Search by ID, fullname or username..."
                   class="filter-input" value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <div class="filter-select-group">
            <select name="sort" class="filter-select">
                <option value="name_asc"  <?php echo (!isset($_GET['sort']) || $_GET['sort'] === 'name_asc')  ? 'selected' : ''; ?>>Name (A → Z)</option>
                <option value="name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'name_desc') ? 'selected' : ''; ?>>Name (Z → A)</option>
                <option value="id_asc"   <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'id_asc')    ? 'selected' : ''; ?>>Staff ID (Asc)</option>
                <option value="id_desc"  <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'id_desc')   ? 'selected' : ''; ?>>Staff ID (Desc)</option>
                <option value="role"     <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'role')      ? 'selected' : ''; ?>>Role</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-primary btn-filter">Search Staff</button>
            <?php if ($search !== "" || (isset($_GET['sort']) && $_GET['sort'] !== 'name_asc')): ?>
                <a href="view_assessors.php" class="clear-link">Clear All</a>
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
            <?php
                    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
        $order_clause = match($sort) {
            'name_desc' => "full_name DESC",
            'id_asc'    => "user_id ASC",
            'id_desc'   => "user_id DESC",
            'role'      => "role ASC, full_name ASC",
            default     => "full_name ASC",
        };

        $sql = "SELECT * FROM users
                WHERE (full_name LIKE '%$search%' OR user_id LIKE '%$search%' OR username LIKE '%$search%')
                AND role IN ('Admin', 'Assessor')
                ORDER BY $order_clause";
            $res = mysqli_query($conn, $sql);

            if (mysqli_num_rows($res) > 0):
                while ($row = mysqli_fetch_assoc($res)):
            ?>
            <tr class="table-row">
                <td class="table-td td-bold-dark"><?php echo htmlspecialchars($row['user_id']); ?></td>
                <td class="table-td td-bold"><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td class="table-td"><?php echo htmlspecialchars($row['username']); ?></td>
                <td class="table-td">
                    <?php if ($row['role'] === 'Admin'): ?>
                        <span style="background: #e0e7ff; color: #3730a3; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: 700;">
                            <?php echo htmlspecialchars($row['role']); ?>
                        </span>
                    <?php else: ?>
                        <?php echo htmlspecialchars($row['role']); ?>
                    <?php endif; ?>
                </td>
               <td class="table-td">
    <?php if ($row['user_id'] !== $_SESSION['user_id']): ?>
        <a href="view_assessors.php?delete=<?php echo urlencode($row['user_id']); ?>"
           class="action-link action-delete"
           onclick="return confirm('Are you sure you want to delete the account for <?php echo htmlspecialchars(addslashes($row['full_name'])); ?>? This will unassign them from all current students.');">
           DELETE
        </a>
    <?php else: ?>
        <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">ACTIVE (YOU)</span>
    <?php endif; ?>
</td>
            </tr>
            <?php
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="5" class="empty-state-td">No staff members found matching your criteria.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include("footer.php"); ?>