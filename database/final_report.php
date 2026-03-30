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
    <title>Final Assessment Report | Internship System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="final-report-page">

<div class="no-print">
    <a href="<?php echo $back_url; ?>" class="dash-back-link">← Back to Dashboard</a>
    <button onclick="window.print()" class="btn-primary" style="padding:12px 25px; border-radius:50px; background: linear-gradient(135deg, #96a3da, #bd94e6); box-shadow: 0 4px 15px rgba(189, 148, 230, 0.3); color:white; border:none; cursor:pointer; font-weight:600; transition: 0.3s;">📥 Download / Print PDF</button>
</div>

<div class="report-paper">
    <div class="report-header">
        <h1>Internship Final Assessment Report</h1>
        <p>Lecturer ID: <strong><?php echo htmlspecialchars($lecturer_id); ?></strong></p>
        <p>Lecturer Name: <strong><?php echo strtoupper(htmlspecialchars($lecturer_name)); ?></strong></p>
        <p>Date: <?php echo date("d-m-Y"); ?></p>
    </div>

    <table class="report-table" style="width:100%; border-collapse:collapse; margin-bottom:50px;">
        <thead>
            <tr style="border-bottom: 2px solid #f3e8ff; text-align:left;">
                <th style="padding:15px; font-size:12px; color:#475569;">STUDENT ID</th>
                <th style="padding:15px; font-size:12px; color:#475569;">NAME</th>
                <th style="padding:15px; font-size:12px; color:#475569;">COMPANY</th>
                <th style="padding:15px; font-size:12px; color:#475569;">MARK</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT s.student_id, s.student_name, i.company_name, a.total_mark 
                      FROM students s 
                      LEFT JOIN internships i ON s.student_id = i.student_id
                      LEFT JOIN assessments a ON i.internship_id = a.internship_id";
            if($role === 'Assessor') $query .= " WHERE s.supervisor_id = '$lecturer_id'";
            $result = mysqli_query($conn, $query);
            while($row = mysqli_fetch_assoc($result)){
                echo "<tr>";
                echo "<td style='padding:15px; font-size:14px; border-bottom: 1px solid #f1f5f9;'>".$row['student_id']."</td>";
                echo "<td style='padding:15px; font-size:14px; border-bottom: 1px solid #f1f5f9;'>".$row['student_name']."</td>";
                echo "<td style='padding:15px; font-size:14px; border-bottom: 1px solid #f1f5f9;'>".($row['company_name'] ?? 'N/A')."</td>";
                echo "<td style='padding:15px; font-size:14px; font-weight:bold; color:#96a3da; border-bottom: 1px solid #f1f5f9;'>".($row['total_mark'] ?? '0')."%</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="signature-container">
        <div class="sig-box">
            <img id="saved-signature" src="" style="display:none; max-height: 500px; margin: 0 auto 5px auto;">
            <div id="signature-board-wrapper">
                <p style="font-size: 11px; color: #64748b; margin-bottom: 8px;">Draw signature below:</p>
                <canvas id="signature-pad" style="width: 100%; height: 160px; border: 1px dashed #cbd5e1; border-radius: 12px; background: #f8fafc; cursor: crosshair;"></canvas>
<div style="margin-top: 25px; margin-bottom: 20px; display: flex; gap: 12px; justify-content: center;">
                    <button type="button" onclick="clearSig()" style="padding: 6px 15px; font-size: 12px; border-radius: 6px; border: 1px solid #ddd; cursor: pointer; background:white;">Clear</button>
                    <button type="button" onclick="saveSig()" style="padding: 6px 15px; font-size: 12px; border-radius: 6px; background: #96a3da; color: white; border:none; cursor: pointer; font-weight: 600;">Confirm</button>
                </div>
            </div>
            <div class="sig-line">Lecturer Signature</div>
            <small style="color: #64748b;"><?php echo strtoupper(htmlspecialchars($lecturer_name)); ?></small>
        </div>

        <div class="sig-box">
            <div class="stamp-wrapper">
                <div class="official-stamp">
                    <span class="stamp-text">INTERNSHIP UNIT</span>
                    <div class="stamp-logo">UMS</div>
                    <span class="stamp-text">VERIFIED OFFICIAL</span>
                </div>
            </div>
            <div class="sig-line">Department Official Stamp</div>
            <small style="color: #64748b;">COMPUTER SCIENCE DEPT.</small>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>