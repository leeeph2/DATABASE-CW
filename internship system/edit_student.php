<?php
// 1. Start Session & Security
session_start();
require("database.php");

// Security Check: Only Admins can edit
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$msg = "";
$student_id = "";

// 2. Grab the Student ID from the URL
if (isset($_GET['id'])) {
    $student_id = mysqli_real_escape_string($conn, $_GET['id']);
} else {
    // If there is no ID in the URL, send them back to the registry
    header("Location: view_students.php");
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

        // Update the Users Table (To keep the login name synced)
        $stmt_u = mysqli_prepare($conn, "UPDATE users SET full_name=? WHERE user_id=?");
        mysqli_stmt_bind_param($stmt_u, "ss", $sname, $student_id);
        mysqli_stmt_execute($stmt_u);

      mysqli_commit($conn);
        $encoded_name = urlencode($sname);
        header("Location: edit_student.php?id=$student_id&msg=updated&name=$encoded_name");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $msg = "<div class='error-notification'>SYSTEM ERROR: Could not update records.</div>";
    }
}

// 4. Fetch the CURRENT student data to pre-fill the form
$query = "SELECT s.*, i.company_name FROM students s LEFT JOIN internships i ON s.student_id = i.student_id WHERE s.student_id = '$student_id'";
$result = mysqli_query($conn, $query);
$student_data = mysqli_fetch_assoc($result);

// If the student doesn't exist, go back to the registry
if (!$student_data) {
    header("Location: view_students.php");
    exit();
}

// Fetch Dropdown Data
$prog_result = mysqli_query($conn, "SELECT * FROM programmes");
$lect_result = mysqli_query($conn, "SELECT user_id, full_name FROM users WHERE role = 'Assessor'");
$company_result = mysqli_query($conn, "SELECT DISTINCT company_name FROM internships WHERE company_name != '' ORDER BY company_name ASC");

// Include Global Header
include("header.php"); 
?>

<div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: flex-end;">
    <div>
        <span class="stat-label">Registry Action</span>
        <h1>Edit Student Profile</h1>
    </div>
    <a href="view_students.php" class="btn-primary" style="margin-bottom: 10px;">← Back to Registry</a>
</div>

<div class="stat-card" style="max-width: 600px; margin: 0 auto; min-height: auto;">
    <p style="color: var(--text-muted); margin-bottom: 30px; font-size: 0.9rem;">
        Updating records for ID: <strong style="color: var(--primary-dark);"><?php echo htmlspecialchars($student_id); ?></strong>
    </p>

    <form method="POST" autocomplete="off">
        
        <div class="form-group">
            <label>Student ID (Cannot be changed)</label>
            <input type="text" value="<?php echo htmlspecialchars($student_data['student_id']); ?>" readonly 
                   style="background: #f1f5f9; color: var(--text-muted); cursor: not-allowed; border-color: transparent;">
        </div>

        <div class="form-group">
            <label>Full Student Name</label>
            <input type="text" name="student_name" required value="<?php echo htmlspecialchars($student_data['student_name']); ?>">
        </div>

        <div class="form-group">
            <label>Academic Programme</label>
            <select name="programme_id" class="form-control" required>
                <option value="">-- Select Programme --</option>
                <?php while($row = mysqli_fetch_assoc($prog_result)): ?>
                    <option value="<?php echo $row['programme_id']; ?>" <?php if($student_data['programme_id'] == $row['programme_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($row['programme_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Assign Assessor</label>
            <select name="supervisor_id" class="form-control" required>
                <option value="">-- Select Lecturer --</option>
                <?php while($row = mysqli_fetch_assoc($lect_result)): ?>
                    <option value="<?php echo $row['user_id']; ?>" <?php if($student_data['supervisor_id'] == $row['user_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($row['full_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 35px;">
            <label>Internship Company</label>
            <select name="company_name" class="form-control" required>
                <option value="">-- Select Company --</option>
                <?php while($row = mysqli_fetch_assoc($company_result)): ?>
                    <option value="<?php echo htmlspecialchars($row['company_name']); ?>" <?php if(($student_data['company_name'] ?? '') == $row['company_name']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($row['company_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%;">Save Updates</button>
    </form>
</div>

<?php 
// Include Global Footer
include("footer.php"); 
?>