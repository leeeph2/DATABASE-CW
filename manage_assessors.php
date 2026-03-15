<?php
session_start();
require("database.php");

// 1. SECURITY
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
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
        // Updated to use floating-alert class
        $message = "<div class='floating-alert error'>You cannot delete your own admin account!</div>";
    } else {
        // --- STEP 1: Fetch the name BEFORE deleting ---
        $name_query = mysqli_query($conn, "SELECT full_name FROM users WHERE user_id = '$target_id'");
        $user_data = mysqli_fetch_assoc($name_query);
        $deleted_name = $user_data['full_name'] ?? "Assessor";

        // --- STEP 2: Now perform the delete ---
        if (mysqli_query($conn, "DELETE FROM users WHERE user_id = '$target_id'")) {
            // Encode the name for the URL
            $encoded_name = urlencode($deleted_name);
            header("Location: manage_assessors.php?msg=deleted&name=$encoded_name");
            exit();
        }
    }
}

// Handle message from redirect
if(isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $name = htmlspecialchars($_GET['name']);
    // Updated to use floating-alert class
    $message = "<div class='floating-alert success'>Assessor '$name' removed successfully.</div>";
}

// 4. ADD ASSESSOR FUNCTIONALITY
if (isset($_POST['add_assessor'])) {
    $uid = mysqli_real_escape_string($conn, $_POST['user_id']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$uid' OR username = '$user'");
    if (mysqli_num_rows($check) > 0) {
        // Updated to use floating-alert class
        $message = "<div class='floating-alert error'>User ID or Username already exists!</div>";
    } else {
        if (mysqli_query($conn, "INSERT INTO users (user_id, username, password, full_name, role) VALUES ('$uid', '$user', '$pass', '$name', 'Assessor')")) {
            // Updated to use floating-alert class
            $message = "<div class='floating-alert success'>Assessor '$name' registered successfully.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Staff | Internship System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="manage-assessors-page">

<div class="container">
    <a href="admin_dashboard.php" class="dash-back-link">← Back to Dashboard</a>
    <br><br>

    <?php if ($message != "") echo $message; ?>

    <div class="glass-card">
        <h2>Register New Assessor</h2>
        <form method="POST" action="manage_assessors.php">
            <div class="form-grid">
                <div class="input-group">
                    <label>Staff ID</label>
                    <input type="text" name="user_id" placeholder="e.g. LEC-005" required>
                </div>
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="e.g. janesmith" required>
                </div>
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="e.g. Dr. Jane Smith" required>
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" name="add_assessor" class="btn-purple" style="margin-top: 20px; width: 100%; border-radius: 14px; padding: 15px;">Add Assessor to Database</button>
        </form>
    </div>

    <div class="glass-card">
        <h2>Existing Staff Management</h2>

        <form method="GET" action="manage_assessors.php" class="search-container">
            <input type="text" name="search_staff" class="search-input" placeholder="Search by name, ID, or username..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit" class="btn-purple">Search</button>
            <?php if($search_query != ""): ?>
                <a href="manage_assessors.php" style="font-size: 12px; color: #94a3b8; text-decoration: none; margin-left: 5px;">Clear</a>
            <?php endif; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM users WHERE (full_name LIKE '%$search_query%' OR user_id LIKE '%$search_query%' OR username LIKE '%$search_query%') ORDER BY role ASC, full_name ASC";
                $res = mysqli_query($conn, $sql);
                
                while ($row = mysqli_fetch_assoc($res)) {
                    echo "<tr>";
                    // Updated Code Tag Color to Purple
                    echo "<td><code style='color: #96a3da; font-weight: 800;'>" . $row['user_id'] . "</code></td>";
                    echo "<td><strong>" . $row['full_name'] . "</strong></td>";
                    echo "<td>" . $row['username'] . "</td>";
                    echo "<td>" . $row['role'] . "</td>";
                    echo "<td style='text-align: right;'>";
                    if ($row['user_id'] !== $_SESSION['user_id']) {
                        // Upgraded the delete link to use the .btn-delete pill styling
                        echo "<a href='manage_assessors.php?delete=" . $row['user_id'] . "' class='btn-delete' onclick='return confirm(\"Delete user?\")'>Delete</a>";
                    } else {
                        echo "<small style='color: #94a3b8;'>Active</small>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>