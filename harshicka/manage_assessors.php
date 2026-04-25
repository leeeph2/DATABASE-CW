<?php
// 1. Start Session & Security
session_start();
require("database.php");

// Security Check: Only Admins can manage staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$message = "";

// 2. SEARCH LOGIC
$search_query = "";
if (isset($_GET['search_staff'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search_staff']);
}

// 3. DELETE FUNCTIONALITY
if (isset($_GET['delete'])) {
    $target_id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Safety check: Don't let the Admin delete themselves
    if ($target_id === $_SESSION['user_id']) {
        $message = "<div class='error-notification' style='padding: 14px; background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; border-radius: 8px; text-align: center; margin-bottom: 20px;'>You cannot delete your own admin account!</div>";
    } else {
        // Fetch the name BEFORE deleting
        $name_query = mysqli_query($conn, "SELECT full_name FROM users WHERE user_id = '$target_id'");
        $user_data = mysqli_fetch_assoc($name_query);
        $deleted_name = $user_data['full_name'] ?? "Assessor";

        // Unassign any students tied to this Assessor first
        // If a staff member is deleted, set their ID to NULL in the internships they are managing
        mysqli_query($conn, "UPDATE internships SET lecturer_id = NULL WHERE lecturer_id = '$target_id'");
        mysqli_query($conn, "UPDATE internships SET supervisor_id = NULL WHERE supervisor_id = '$target_id'");

        // Safe delete
        if (mysqli_query($conn, "DELETE FROM users WHERE user_id = '$target_id'")) {
            $encoded_name = urlencode($deleted_name);
            header("Location: manage_assessors.php?msg=removed&name=$encoded_name");
            exit();
        } else {
            $message = "<div class='error-notification' style='padding: 14px; background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; border-radius: 8px; text-align: center; margin-bottom: 20px;'>Database error: Could not delete user.</div>";
        }
    }
}

// 4. ADD ASSESSOR FUNCTIONALITY
if (isset($_POST['add_assessor'])) {
    // Sanitize inputs
    $uid = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['user_id'])));
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']); // Capture Lecturer or Supervisor
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validate if the role is allowed by your ENUM
    $allowed_roles = ['Lecturer', 'Supervisor'];
    
    // Check if ID or Username already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$uid' OR username = '$user'");
    
    if (!in_array($role, $allowed_roles)) {
        $message = "<div class='error-notification' style='padding: 14px; background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; border-radius: 8px; text-align: center; margin-bottom: 20px;'>Error: Invalid role selected.</div>";
    } elseif (mysqli_num_rows($check) > 0) {
        $message = "<div class='error-notification' style='padding: 14px; background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; border-radius: 8px; text-align: center; margin-bottom: 20px;'>Error: User ID or Username already exists!</div>";
    } else {
        // Updated INSERT query to use the dynamic $role variable
        $insert_sql = "INSERT INTO users (user_id, username, password, full_name, role) 
                       VALUES ('$uid', '$user', '$pass', '$name', '$role')";
        
        if (mysqli_query($conn, $insert_sql)) {
            $encoded_name = urlencode($name);
            header("Location: manage_assessors.php?msg=registered&name=$encoded_name");
            exit();
        } else {
            $message = "<div class='error-notification' style='padding: 14px; background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; border-radius: 8px; text-align: center; margin-bottom: 20px;'>Database Error: Could not add user.</div>";
        }
    }
}

// Include Global Header
include("header.php"); 
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid var(--border); padding-bottom: 20px; margin-bottom: 40px;">
    <div>
        <span class="stat-label">Faculty Management</span>
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--primary-dark); margin: 5px 0 15px 0;">Assessor Records</h1>
        <a href="admin_dashboard.php" class="back-link">
            ← Back to Dashboard
        </a>
    </div>
</div>

<div class="stat-card" style="margin-bottom: 40px; min-height: auto;">
    <h2 style="font-size: 1.25rem; color: var(--primary-dark); margin-bottom: 20px;">Register New Faculty Staff</h2>
    <form method="POST" action="manage_assessors.php">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Staff ID</label>
                <input type="text" name="user_id" placeholder="e.g. LEC-005" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Username</label>
                <input type="text" name="username" placeholder="e.g. janesmith" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Full Name</label>
                <input type="text" name="full_name" placeholder="e.g. Dr. Jane Smith" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Staff Role</label>
                <select name="role" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; background: white;">
                    <option value="" disabled selected>Select Role</option>
                    <option value="Lecturer">Lecturer</option>
                    <option value="Supervisor">Supervisor</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
        </div>
        <button type="submit" name="add_assessor" class="btn-primary" style="margin-top: 25px;">Add Staff to Database</button>
    </form>
</div>

<div class="stat-card" style="padding: 0; overflow: hidden; min-height: auto;">
    
    <div style="padding: 24px; border-bottom: 2px solid var(--border); background-color: #f8fafc;">
        <form method="GET" action="manage_assessors.php" style="display: flex; gap: 15px; align-items: center;">
            <input type="text" name="search_staff" placeholder="Search by name, ID, or username..." value="<?php echo htmlspecialchars($search_query); ?>" 
                   style="flex-grow: 1; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit;">
            <button type="submit" class="btn-primary">Search Staff</button>
            <?php if($search_query != ""): ?>
                <a href="manage_assessors.php" style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-decoration: none;">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border);">
                <th style="padding: 16px 24px; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Staff ID</th>
                <th style="padding: 16px 24px; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Full Name</th>
                <th style="padding: 16px 24px; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Username</th>
                <th style="padding: 16px 24px; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Role</th>
                <th style="padding: 16px 24px; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM users WHERE (full_name LIKE '%$search_query%' OR user_id LIKE '%$search_query%' OR username LIKE '%$search_query%') AND role IN ('Admin', 'Lecturer', 'Supervisor') 
        ORDER BY role ASC, full_name ASC";
            $res = mysqli_query($conn, $sql);
            
            if (mysqli_num_rows($res) > 0) {
                while ($row = mysqli_fetch_assoc($res)) {
                    echo "<tr style='border-bottom: 1px solid var(--border); transition: background-color 0.2s;'>";
                    echo "<td style='padding: 16px 24px; font-weight: 700; color: var(--primary-dark);'>" . htmlspecialchars($row['user_id']) . "</td>";
                    echo "<td style='padding: 16px 24px; font-weight: 600;'>" . htmlspecialchars($row['full_name']) . "</td>";
                    echo "<td style='padding: 16px 24px; color: var(--text-muted); font-size: 0.9rem;'>" . htmlspecialchars($row['username']) . "</td>";
                    
                    // Highlight Admin role slightly
                    if ($row['role'] === 'Admin') {
                        echo "<td style='padding: 16px 24px;'><span style='background: #e0e7ff; color: #3730a3; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 700;'>" . $row['role'] . "</span></td>";
                    } else {
                        echo "<td style='padding: 16px 24px; color: var(--text-muted); font-size: 0.9rem;'>" . $row['role'] . "</td>";
                    }
                    
                    echo "<td style='padding: 16px 24px;'>";
                    
                    // Edit Button (If you have an edit_assessor.php file)
                    // echo "<a href='edit_assessor.php?id=" . urlencode($row['user_id']) . "' style='color: var(--primary); font-weight: 700; font-size: 0.85rem; text-decoration: none; margin-right: 15px;'>EDIT</a>";

                    // Delete Button
                    if ($row['user_id'] !== $_SESSION['user_id']) {
                        echo "<a href='manage_assessors.php?delete=" . urlencode($row['user_id']) . "' onclick='return confirm(\"Are you sure you want to delete this user? This will unassign them from all current students.\")' style='color: var(--status-red); font-weight: 700; font-size: 0.85rem; text-decoration: none;'>DELETE</a>";
                    } else {
                        echo "<span style='color: var(--text-muted); font-size: 0.85rem; font-weight: 700;'>ACTIVE (YOU)</span>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='padding: 40px; text-align: center; color: var(--text-muted); font-weight: 600;'>No staff members found matching your criteria.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php 
// Include Global Footer
include("footer.php"); 
?>