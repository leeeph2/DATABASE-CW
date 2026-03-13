<?php
session_start();
include('database.php');

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Assessor') {
    header("Location: login.php");
    exit();
}

$current_lec = $_SESSION['user_id']; // This is 'LEC-001'

// Fetch only students assigned to THIS lecturer
$query = "SELECT s.student_id, s.student_name, p.programme_name, i.company_name, i.internship_id
          FROM students s
          JOIN programmes p ON s.programme_id = p.programme_id
          JOIN internships i ON s.student_id = i.student_id
          WHERE s.supervisor_id = '$current_lec'";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Assessor Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>My Supervision List</h1>
    <p>Lecturer ID: <?php echo $current_lec; ?></p>

    <table border="1">
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Programme</th>
            <th>Company</th>
            <th>Action</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo $row['student_id']; ?></td>
            <td><?php echo $row['student_name']; ?></td>
            <td><?php echo $row['programme_name']; ?></td>
            <td><?php echo $row['company_name']; ?></td>
            <td>
                <a href="evaluate.php?id=<?php echo $row['internship_id']; ?>">Grade Now</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="logout.php">Logout</a>
</body>
</html>