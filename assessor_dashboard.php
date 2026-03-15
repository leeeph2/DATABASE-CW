<?php
session_start();
require("database.php");

// 1. Security Check: Only Assessors can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assessor') {
    header("Location: index.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];
$lecturer_name = $_SESSION['username'];

// 2. Search Logic (Only searches THEIR students)
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

// 3. Fetch ONLY students assigned to this specific Assessor
$query = "SELECT s.student_id, s.student_name, i.company_name, a.total_mark 
          FROM students s
          LEFT JOIN internships i ON s.student_id = i.student_id
          LEFT JOIN assessments a ON i.internship_id = a.internship_id
          WHERE s.supervisor_id = '$lecturer_id' 
          AND (s.student_id LIKE '%$search%' OR s.student_name LIKE '%$search%')
          ORDER BY s.student_name ASC";
$result = mysqli_query($conn, $query);

// Count total students for the stats card
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM students WHERE supervisor_id = '$lecturer_id'");
$count_data = mysqli_fetch_assoc($count_query);
$total_students = $count_data['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessor Dashboard | Internship System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>

<div class="dashboard-container">
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
        <div>
            <h1 style="margin: 0; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;">System Administration</h1>
            <p style="color: #64748b; margin-top: 5px; font-size: 15px;">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
        </div>
        
        <a href="logout.php" style="color: -webkit-link; font-weight: 500; text-decoration: underline;">Logout</a>
    </header>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Assigned Students</div>
            <div class="stat-number"><?php echo $total_students; ?></div>
        </div>
        <div class="stat-card" style="display: flex; flex-direction: column; justify-content: center;">
            <a href="final_report.php" class="btn-add" style="text-decoration: none; text-align: center; margin-top: 10px;">📄 Generate Final Report</a>
        </div>
    </div>

    <div class="glass-card">
        <h2 style="margin-top: 0; margin-bottom: 25px; color: #0f172a;">My Assigned Students</h2>
        
        <form method="GET" action="assessor_dashboard.php" class="search-container">
            <input type="text" name="search" class="search-input" placeholder="Search your students by ID or Name..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" style="padding: 10px; cursor: pointer;">Search</button>
            <?php if($search != ""): ?>
                <a href="assessor_dashboard.php" style="font-size: 12px; color: #64748b; text-decoration: none; align-self: center; margin-left: 10px;">Clear</a>
            <?php endif; ?>
        </form>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Company Name</th>
                        <th>Current Mark</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)): 
                            $mark = $row['total_mark'];
                            $has_mark = ($mark !== null && $mark !== '');
                    ?>
                    <tr>
                        <td><code style="color: #96a3da; font-weight: 800;"><?php echo $row['student_id']; ?></code></td>
                        <td><strong><?php echo $row['student_name']; ?></strong></td>
                        <td><?php echo $row['company_name'] ?? '<span style="color:#94a3b8">Not Assigned</span>'; ?></td>
                        <td>
                            <?php echo $has_mark ? "<strong style='color:#96a3da;'>".$mark."%</strong>" : "<span style='color:#94a3b8'>--</span>"; ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if($has_mark): ?>
                                <span class="shared-pill badge-graded">Graded</span>
                            <?php else: ?>
                                <span class="shared-pill badge-pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <a href="evaluate_student.php?id=<?php echo $row['student_id']; ?>" class="shared-pill action-btn edit-purple">
                                <?php echo $has_mark ? 'Edit Marks' : 'Evaluate'; ?>
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; padding: 40px; color:#94a3b8;'>No students assigned to you yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>