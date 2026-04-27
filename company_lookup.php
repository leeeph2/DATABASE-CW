<?php
require("database.php");

$student_data = null;
$error = "";

if (isset($_POST['view_report'])) {
    $sid = mysqli_real_escape_string($conn, $_POST['student_id']);
    
    // Query to fetch student details and their calculated marks
    $query = "SELECT s.student_name, i.company_name, a.total_mark, a.comments 
              FROM students s
              JOIN internships i ON s.student_id = i.student_id
              LEFT JOIN assessments a ON i.internship_id = a.internship_id
              WHERE s.student_id = '$sid'";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $student_data = mysqli_fetch_assoc($result);
    } else {
        $error = "No record found for Student ID: " . htmlspecialchars($sid);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employer Portal | Internship Progress</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="padding: 40px; background: #f8fafc;">

    <div style="max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <h2 style="color: #1e293b;">Employer Verification Portal</h2>
        <p style="color: #64748b;">Enter the Student ID provided by the university to view current internship progress.</p>
        
        <form method="POST" style="margin-top: 20px;">
            <input type="text" name="student_id" placeholder="e.g. STU-101" required 
                   style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 10px;">
            <button type="submit" name="view_report" 
                    style="width: 100%; padding: 12px; background: #96a3da; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                Search Student Records
            </button>
        </form>

        <?php if ($error): ?>
            <p style="color: #ef4444; margin-top: 20px; font-weight: 500;"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if ($student_data): ?>
            <div style="margin-top: 30px; border-top: 2px solid #f1f5f9; padding-top: 20px;">
                <h3 style="color: #1e293b;">Assessment Results</h3>
                <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student_data['student_name']); ?></p>
                <p><strong>Host Company:</strong> <?php echo htmlspecialchars($student_data['company_name']); ?></p>
                
                <div style="background: #f1f5f9; padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <span style="display: block; color: #64748b; font-size: 0.9em;">Current Overall Mark</span>
                    <span style="font-size: 28px; color: #96a3da; font-weight: 800;">
                        <?php echo $student_data['total_mark'] ? $student_data['total_mark'] . "%" : "Not yet graded"; ?>
                    </span>
                </div>

                <p><strong>University Feedback:</strong></p>
                <p style="font-style: italic; color: #475569; background: #fffbeb; padding: 10px; border-left: 4px solid #f59e0b;">
                    "<?php echo htmlspecialchars($student_data['comments'] ?? "The assigned lecturer has not yet finalized the qualitative feedback."); ?>"
                </p>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color: #94a3b8; text-decoration: none; font-size: 0.9em;">← Return to Login</a>
        </div>
    </div>

</body>
</html>