<?php
session_start();
require("database.php");

// 1. Protection & Role-based Back Link
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];
$lecturer_name = $_SESSION['username'];
$role = $_SESSION['role'];

// Determine which dashboard to go back to
$back_url = ($role === 'Admin') ? "admin_dashboard.php" : "assessor_dashboard.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Final Assessment Report</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #fff; color: #333; padding: 50px; }
        .report-header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { border-bottom: 2px solid #444; padding: 12px; text-align: left; text-transform: uppercase; font-size: 11px; letter-spacing: 1px; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .mark { font-weight: bold; color: #2c3e50; }
        .status-completed { color: #27ae60; font-weight: bold; }
        .status-pending { color: #e67e22; }
        .no-print { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .print-btn { background: #333; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            table { font-size: 12px; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <a href="<?php echo $back_url; ?>" style="color: #666; text-decoration: none; font-weight: bold;">← Back to Dashboard</a>
    <button onclick="window.print()" class="print-btn">Download / Print PDF</button>
</div>

<div class="report-header">
    <h1>Internship Final Assessment Report</h1>
    <p>Lecturer ID: <strong><?php echo $lecturer_id; ?></strong> | Lecturer Name: <strong><?php echo strtoupper($lecturer_name); ?></strong></p>
    <p>Date: <?php echo date("d-m-Y"); ?></p>
</div>

<table>
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Company</th>
            <th>Final Mark</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Updated Query: Joins Students with Internships and Assessments
        // We use LEFT JOIN so students appear even if they haven't been graded yet
        $query = "SELECT 
                    s.student_id, 
                    s.student_name, 
                    i.company_name, 
                    a.total_mark,
                    i.internship_status
                  FROM students s
                  LEFT JOIN internships i ON s.student_id = i.student_id
                  LEFT JOIN assessments a ON i.internship_id = a.internship_id";
        
        // If the user is an Assessor, they should only see THEIR students' report
        if ($role === 'Assessor') {
            $query .= " WHERE s.supervisor_id = '$lecturer_id'";
        }
        
        $query .= " ORDER BY s.student_name ASC";
        
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $mark = ($row['total_mark'] !== null) ? number_format($row['total_mark'], 2) . "%" : "N/A";
                $status_class = ($row['internship_status'] === 'Evaluated') ? "status-completed" : "status-pending";
                $status_text = ($row['internship_status'] === 'Evaluated') ? "GRADED" : "PENDING";

                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['student_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['company_name'] ?? 'Not Assigned') . "</td>";
                echo "<td class='mark'>$mark</td>";
                echo "<td class='$status_class'>$status_text</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5' style='text-align:center; padding: 40px;'>No student records found under your supervision.</td></tr>";
        }
        ?>
    </tbody>
</table>

<div style="margin-top: 80px; display: flex; justify-content: space-between;">
    <div style="text-align: center;">
        <div style="border-top: 1px solid #333; width: 220px; padding-top: 5px; margin-bottom: 5px;">Lecturer Signature</div>
        <small style="color: #888;"><?php echo strtoupper($lecturer_name); ?></small>
    </div>
    <div style="text-align: center;">
        <div style="border-top: 1px solid #333; width: 220px; padding-top: 5px;">Department Official Stamp</div>
    </div>
</div>

</body>
</html>