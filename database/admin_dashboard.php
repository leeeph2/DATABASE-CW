<?php
session_start();
require("database.php");

// 1. Security Check: Only Admins allowed [cite: 13]
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

// 2. Search Logic 
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

// 3. Fetching Students with Program and Supervisor Info [cite: 14, 20, 25]
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
    <style>
        /* Minimalist internal styles to guide your teammate */
        .dashboard-container { padding: 40px; font-family: 'Segoe UI', sans-serif; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border: 1px solid #eee; border-radius: 4px; text-align: center; }
        .search-bar { margin-bottom: 20px; display: flex; gap: 10px; }
        .search-input { padding: 10px; border: 1px solid #ddd; width: 300px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; text-transform: uppercase; font-size: 12px; }
        .btn-add { background: #333; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-size: 14px; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <header style="display: flex; justify-content: space-between; align-items: center;">
        <h1>System Administration</h1>
        <a href="logout.php" style="color: #e74c3c;">Logout</a>
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
        <form method="GET" action="admin_dashboard.php">
            <input type="text" name="search" class="search-input" placeholder="Search by ID or Name..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" style="padding: 10px;">Search</button>
            <?php if($search != ""): ?>
                <a href="admin_dashboard.php" style="font-size: 12px; align-self: center;">Clear Search</a>
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
                    <a href="edit_student.php?id=<?php echo $row['student_id']; ?>">Edit</a> | 
                    <a href="delete_student.php?id=<?php echo $row['student_id']; ?>" onclick="return confirm('Delete this record?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>