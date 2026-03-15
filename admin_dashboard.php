<?php
session_start();
require("database.php");

// 1. Security Check: Only Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

// 2. Search Logic (This is what accidentally got deleted!)
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

// 3. Listen for the delete message and grab the name
$message = "";
if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $deleted_name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Student';
    $message = "<div class='floating-alert success'>Student '$deleted_name' deleted successfully.</div>";
}

// 4. Fetching Students with Program and Supervisor Info
$query = "SELECT s.student_id, s.student_name, p.programme_name, u.full_name AS supervisor_name, i.company_name
          FROM students s
          LEFT JOIN programmes p ON s.programme_id = p.programme_id
          LEFT JOIN users u ON s.supervisor_id = u.user_id
          LEFT JOIN internships i ON s.student_id = i.student_id
          WHERE s.student_id LIKE '%$search%' OR s.student_name LIKE '%$search%'
          ORDER BY s.student_name ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Internship Management</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<body>

<?php if ($message != "") echo $message; ?>

<div class="dashboard-container">

<div class="dashboard-container">
    <header style="display: flex; justify-content: space-between; align-items: center;">
        <h1>System Administration</h1>
        <a href="logout.php" style="color: -webkit-link; font-weight: 500; text-decoration: underline;">Logout</a>
    </header>
    
    <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>

    <div class="stats-grid">
        <div class="stat-card">
            <a href="add_student.php" class="btn-add">+ Add New Student</a>
        </div>
        <div class="stat-card">
            <a href="manage_assessors.php" class="btn-add">Manage Assessors</a>
        </div>
        <div class="stat-card">
            <a href="final_report.php" class="btn-add">View Final Reports</a>
        </div>
    </div>

   <div class="search-bar">
        <form method="GET" action="admin_dashboard.php" class="search-container">
            
            <input type="text" name="search" class="search-input" placeholder="Search by ID or Name..." value="<?php echo htmlspecialchars($search); ?>">
            
            <button type="submit" style="padding: 10px; cursor: pointer;">Search</button>
            
            <?php if($search != ""): ?>
                <a href="admin_dashboard.php" style="font-size: 12px; color: #64748b; text-decoration: none; align-self: center; margin-left: 10px;">Clear Search</a>
            <?php endif; ?>
            
        </form>
    </div>

    <h3>Student Records Management</h3>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Programme</th>
                <th>Company</th>
                <th>Assigned Assessor</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row['student_id']; ?></td>
                <td><?php echo $row['student_name']; ?></td>
                <td><?php echo $row['programme_name'] ?? 'Unassigned'; ?></td>
                <td><?php echo $row['company_name'] ?? 'No Internship'; ?></td>
                <td><?php echo $row['supervisor_name'] ?? '<em>Unassigned</em>'; ?></td>
                <td>
                    <div class="action-buttons">
                        <a href="edit_student.php?id=<?php echo $row['student_id']; ?>" class="btn-edit">Edit</a>
                        <a href="delete_student.php?id=<?php echo $row['student_id']; ?>" class="btn-delete" onclick="return confirm('Delete this record?')">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>