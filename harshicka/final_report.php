<?php
session_start();
require("database.php");

// 1. Protection & Role-based Back Link
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=unauthorized");
    exit();
}

$lecturer_id = $_SESSION['user_id'];
$lecturer_name = $_SESSION['username'];
$role = $_SESSION['role'];

$back_url = ($role === 'Admin') ? "admin_dashboard.php" : "assessor_dashboard.php";

// Include Global Header
include("header.php");
?>

<div class="page-header no-print" style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid var(--border-color); padding-bottom: 20px; margin-bottom: 40px;">
    <div>
        <span class="stat-label">System Output</span>
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--primary-dark); margin: 5px 0 15px 0;">Final Assessment Report</h1>
       <a href="admin_dashboard.php" class="back-link">
            ← Back to Dashboard
        </a>
    </div>
    <button onclick="window.print()" class="btn-primary" style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
        Print Document
    </button>
</div>

<div class="report-paper">
    
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: var(--primary-dark); margin-bottom: 10px; font-size: 28px;">Internship Final Assessment Report</h1>
        <p style="color: var(--text-muted); font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">University of Nottingham Malaysia</p>
    </div>

    <div style="display: flex; justify-content: space-between; margin-bottom: 40px; padding: 25px; background: #f8fafc; border-radius: 8px; border: 1px solid var(--border-color);">
        <div>
            <p style="margin-bottom: 8px; color: var(--text-muted); font-size: 14px;">Lecturer ID: <strong style="color: var(--primary-dark); font-size: 16px;"><?php echo htmlspecialchars($lecturer_id); ?></strong></p>
            <p style="color: var(--text-muted); font-size: 14px;">Lecturer Name: <strong style="color: var(--primary-dark); font-size: 16px;"><?php echo strtoupper(htmlspecialchars($lecturer_name)); ?></strong></p>
        </div>
        <div style="text-align: right;">
            <p style="color: var(--text-muted); font-size: 14px;">Date Generated: <strong style="color: var(--text-main);"><?php echo date("d-m-Y"); ?></strong></p>
        </div>
    </div>

    <table style="width:100%; border-collapse:collapse; margin-bottom:60px;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border-color); text-align:left;">
                <th style="padding:15px; font-size:12px; color:var(--text-muted); text-transform: uppercase;">Student ID</th>
                <th style="padding:15px; font-size:12px; color:var(--text-muted); text-transform: uppercase;">Name</th>
                <th style="padding:15px; font-size:12px; color:var(--text-muted); text-transform: uppercase;">Company</th>
                <th style="padding:15px; font-size:12px; color:var(--text-muted); text-transform: uppercase;">Final Mark</th>
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
            
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)){
                    echo "<tr style='border-bottom: 1px solid #f1f5f9;'>";
                    echo "<td style='padding:15px; font-size:15px; font-weight:700; color: var(--primary-dark);'>".$row['student_id']."</td>";
                    echo "<td style='padding:15px; font-size:15px; font-weight:600;'>".$row['student_name']."</td>";
                    echo "<td style='padding:15px; font-size:14px; color:var(--text-muted);'>".($row['company_name'] ?? 'N/A')."</td>";
                    echo "<td style='padding:15px; font-size:16px; font-weight:800; color:var(--primary-blue);'>".($row['total_mark'] ?? '0')."%</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='padding: 30px; text-align: center; color: var(--text-muted);'>No finalized assessments available for printing.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; padding-top: 20px;">
    
    <div class="sig-box">
        <img id="saved-signature" src="" style="display:none; max-height: 120px; margin: 0 auto 5px auto;">
        
        <div id="signature-board-wrapper" class="no-print">
            <p style="font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">Draw signature below:</p>
            <canvas id="signature-pad" style="width: 90%; height: 200px; border: 1px dashed var(--border-color); border-radius: 8px; background: #f8fafc; cursor: crosshair;"></canvas>
            <div style="margin-top: 15px; margin-bottom: 20px; display: flex; gap: 10px; justify-content: center;">
                <button type="button" onclick="clearSig()" style="padding: 8px 15px; font-size: 12px; border-radius: 6px; border: 1px solid var(--border-color); cursor: pointer; background:white; font-weight: 600;">Clear</button>
                <button type="button" onclick="saveSig()" class="btn-primary" style="padding: 8px 15px; font-size: 12px; cursor: pointer; width: auto;">Confirm Signature</button>
            </div>
        </div>

        <div id="confirmed-sig-container" style="display:none; text-align:center;" class="no-print">
            <p style="font-size: 11px; color: green; margin-bottom: 8px;">✓ Signature confirmed</p>
            <button type="button" onclick="deleteSig()" style="padding: 6px 12px; font-size: 11px; border-radius: 6px; border: 1px solid var(--border-color); cursor: pointer; background:white; font-weight: 600;">Re-sign</button>
        </div>
        
        <div class="sig-line">Lecturer Signature</div>
        <small style="color: var(--text-muted);"><?php echo strtoupper(htmlspecialchars($lecturer_name)); ?></small>
    </div>

    <div class="sig-box">
        <div class="official-stamp">
            <span class="stamp-text">INTERNSHIP UNIT</span>
            <div class="stamp-logo">UNM</div>
            <span class="stamp-text">VERIFIED OFFICIAL</span>
        </div>
        <div class="sig-line">Department Official Stamp</div>
        <small style="color: var(--text-muted);">COMPUTER SCIENCE DEPT.</small>
    </div>

</div>
</div>

<script src="script.js"></script>

<?php include("footer.php"); ?>