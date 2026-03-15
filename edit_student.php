<?php
session_start();
require("database.php");

// 1. Security Check: Only Admins can edit
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

$msg = "";
$student_id = "";

// 2. Grab the Student ID from the URL
if (isset($_GET['id'])) {
    $student_id = mysqli_real_escape_string($conn, $_GET['id']);
} else {
    // If there is no ID in the URL, send them back to the dashboard
    header("Location: admin_dashboard.php");
    exit();
}

// 3. Handle the Form Submission (Updating the database)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sname = mysqli_real_escape_string($conn, $_POST['student_name']);
    $pid = $_POST['programme_id'];
    $lid = $_POST['supervisor_id'];
    $company = mysqli_real_escape_string($conn, $_POST['company_name']);

    mysqli_begin_transaction($conn);

    try {
        // Update the Students Table
        $stmt_s = mysqli_prepare($conn, "UPDATE students SET student_name=?, programme_id=?, supervisor_id=? WHERE student_id=?");
        mysqli_stmt_bind_param($stmt_s, "ssss", $sname, $pid, $lid, $student_id);
        mysqli_stmt_execute($stmt_s);

        // Update the Internships Table
        $stmt_i = mysqli_prepare($conn, "UPDATE internships SET company_name=? WHERE student_id=?");
        mysqli_stmt_bind_param($stmt_i, "ss", $company, $student_id);
        mysqli_stmt_execute($stmt_i);

        // Update the Users Table (To keep the login name synced with the student name)
        $stmt_u = mysqli_prepare($conn, "UPDATE users SET full_name=? WHERE user_id=?");
        mysqli_stmt_bind_param($stmt_u, "ss", $sname, $student_id);
        mysqli_stmt_execute($stmt_u);

        mysqli_commit($conn);
        $msg = "<div class='alert alert-success'>SUCCESS: Student records updated successfully!</div>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $msg = "<div class='alert alert-error'>SYSTEM ERROR: Could not update records.</div>";
    }
}

// 4. Fetch the CURRENT student data to pre-fill the form
$query = "SELECT s.*, i.company_name FROM students s LEFT JOIN internships i ON s.student_id = i.student_id WHERE s.student_id = '$student_id'";
$result = mysqli_query($conn, $query);
$student_data = mysqli_fetch_assoc($result);

// If the student doesn't exist, go back to dashboard
if (!$student_data) {
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch Dropdown Data
$prog_result = mysqli_query($conn, "SELECT * FROM programmes");
$lect_result = mysqli_query($conn, "SELECT user_id, full_name FROM users WHERE role = 'Assessor'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student | Admin Portal</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="add-student-page">

<div class="form-card">
    <a href="admin_dashboard.php" class="dash-back-link">← Back to Dashboard</a>
    <h2>Edit Student Profile</h2>
    <p class="subtitle">Updating records for ID: <strong style="color: #96a3da;"><?php echo htmlspecialchars($student_id); ?></strong></p>

    <?php if($msg != "") echo $msg; ?>

    <form method="POST" autocomplete="off">
        
        <div class="input-group">
            <label>Student ID (Cannot be changed)</label>
            <input type="text" value="<?php echo htmlspecialchars($student_data['student_id']); ?>" readonly style="background: rgba(0,0,0,0.05); color: #64748b; cursor: not-allowed;">
        </div>

        <div class="input-group">
            <label>Full Student Name</label>
            <input type="text" name="student_name" required value="<?php echo htmlspecialchars($student_data['student_name']); ?>">
        </div>

        <div class="input-group">
            <label>Academic Programme</label>
            <select name="programme_id" required>
                <option value="">-- Select Programme --</option>
                <?php while($row = mysqli_fetch_assoc($prog_result)): ?>
                    <option value="<?php echo $row['programme_id']; ?>" <?php if($student_data['programme_id'] == $row['programme_id']) echo 'selected'; ?>>
                        <?php echo $row['programme_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="input-group">
            <label>Assign Assessor</label>
            <select name="supervisor_id" required>
                <option value="">-- Select Lecturer --</option>
                <?php while($row = mysqli_fetch_assoc($lect_result)): ?>
                    <option value="<?php echo $row['user_id']; ?>" <?php if($student_data['supervisor_id'] == $row['user_id']) echo 'selected'; ?>>
                        <?php echo $row['full_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="input-group">
            <label>Internship Company</label>
            <input type="text" name="company_name" required value="<?php echo htmlspecialchars($student_data['company_name'] ?? ''); ?>">
        </div>

        <button type="submit" class="btn-save">Save Updates</button>
    </form>
</div>

</body>
</html>