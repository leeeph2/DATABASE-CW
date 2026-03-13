<?php
session_start();
require("database.php");

// 1. Security: Only Admins can add students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

// 2. Fetch Data for Dropdowns
$prog_result = mysqli_query($conn, "SELECT * FROM programmes");
$lect_result = mysqli_query($conn, "SELECT user_id, full_name FROM users WHERE role = 'Assessor' LIMIT 5");

$msg = "";

// 3. Process Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sid = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['student_id'])));
    $sname = mysqli_real_escape_string($conn, $_POST['student_name']);
    $pid = $_POST['programme_id'];
    $lid = $_POST['supervisor_id'];
    $company = mysqli_real_escape_string($conn, $_POST['company_name']);
    
    // Default password for new students (they should change this later)
    $default_pass = password_hash("student123", PASSWORD_DEFAULT);

    // 4. Check if Student ID already exists
    $check = mysqli_query($conn, "SELECT student_id FROM students WHERE student_id = '$sid'");
    if (mysqli_num_rows($check) > 0) {
        $msg = "<p style='color:red;'>Error: Student ID already exists in the system.</p>";
    } else {
        // 5. Start Transaction for Data Integrity
        mysqli_begin_transaction($conn);

        try {
            // A. Create User Account (so they can log in to see results)
            $stmt_u = mysqli_prepare($conn, "INSERT INTO users (user_id, username, password, full_name, role) VALUES (?, ?, ?, ?, 'Student')");
            mysqli_stmt_bind_param($stmt_u, "ssss", $sid, $sid, $default_pass, $sname);
            mysqli_stmt_execute($stmt_u);

            // B. Insert Student Profile
            $stmt_s = mysqli_prepare($conn, "INSERT INTO students (student_id, student_name, programme_id, supervisor_id) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_s, "ssss", $sid, $sname, $pid, $lid);
            mysqli_stmt_execute($stmt_s);

            // C. Create Internship Record
            $int_id = "INT-" . substr($sid, -3); 
            $stmt_i = mysqli_prepare($conn, "INSERT INTO internships (internship_id, student_id, company_name, internship_status) VALUES (?, ?, ?, 'Pending')");
            mysqli_stmt_bind_param($stmt_i, "sss", $int_id, $sid, $company);
            mysqli_stmt_execute($stmt_i);

            mysqli_commit($conn);
            $msg = "<p style='color:green;'>SUCCESS: Student account, profile, and internship created!</p>";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $msg = "<p style='color:red;'>SYSTEM ERROR: Transaction failed. Please check database logs.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Student | Admin Portal</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 40px; color: #333; }
        .form-card { max-width: 550px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .back-link { display: inline-block; margin-bottom: 20px; color: #555; text-decoration: none; font-size: 14px; }
        .back-link:hover { text-decoration: underline; }
        h2 { margin-bottom: 5px; color: #1a1a1a; }
        p.subtitle { color: #666; font-size: 14px; margin-bottom: 25px; }
        .input-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; text-transform: uppercase; color: #444; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 15px; transition: border 0.3s; }
        input:focus { border-color: #333; outline: none; }
        .btn-save { width: 100%; background: #1a1a1a; color: #fff; border: none; padding: 14px; cursor: pointer; border-radius: 6px; font-size: 16px; font-weight: 600; transition: background 0.3s; }
        .btn-save:hover { background: #000; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
    </style>
</head>
<body>

<div class="form-card">
    <a href="admin_dashboard.php" class="back-link">← Return to Dashboard</a>
    <h2>Register New Student</h2>
    <p class="subtitle">This will create a user account and an internship entry.</p>

    <?php if($msg != "") echo "<div class='alert'>$msg</div>"; ?>

    <form method="POST" autocomplete="off">
        <div class="input-group">
            <label>Student ID</label>
            <input type="text" name="student_id" required placeholder="e.g. STU-101" pattern="[A-Za-z0-9\-]{3,10}">
        </div>

        <div class="input-group">
            <label>Full Student Name</label>
            <input type="text" name="student_name" required placeholder="Enter formal name">
        </div>

        <div class="input-group">
            <label>Academic Programme</label>
            <select name="programme_id" required>
                <option value="">-- Select Programme --</option>
                <?php while($row = mysqli_fetch_assoc($prog_result)): ?>
                    <option value="<?php echo $row['programme_id']; ?>"><?php echo $row['programme_name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="input-group">
            <label>Assign Assessor</label>
            <select name="supervisor_id" required>
                <option value="">-- Select Lecturer --</option>
                <?php while($row = mysqli_fetch_assoc($lect_result)): ?>
                    <option value="<?php echo $row['user_id']; ?>"><?php echo $row['full_name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="input-group">
            <label>Internship Company</label>
            <input type="text" name="company_name" required placeholder="Name of organization">
        </div>

        <button type="submit" class="btn-save">Create Student Records</button>
    </form>
</div>

</body>
</html>