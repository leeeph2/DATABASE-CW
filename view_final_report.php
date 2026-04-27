<?php
session_start();
require("database.php");

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'] ?? 'User';

// Determine dynamic link back to the correct dashboard
$dash_link = match(ucfirst(strtolower(trim($role)))) {
    'Lecturer'   => 'lecturer_dashboard.php',
    'Supervisor' => 'supervisor_dashboard.php',
    'Admin'      => 'admin_dashboard.php',
    default      => 'index.php',
};

// 2. Fetch Report Data
if (isset($_GET['id'])) {
    $internship_id = mysqli_real_escape_string($conn, $_GET['id']);

    $info_q = mysqli_query($conn, "
        SELECT s.student_name, s.student_id, i.company_name, i.internship_id
        FROM internships i
        JOIN students s ON i.student_id = s.student_id
        WHERE i.internship_id = '$internship_id'
    ");
    $info = mysqli_fetch_assoc($info_q);

    $assess_q = mysqli_query($conn, "SELECT * FROM assessments WHERE internship_id = '$internship_id'");
    $evals = [];
    while ($row = mysqli_fetch_assoc($assess_q)) {
        $evals[$row['assessment_type']] = $row;
    }
} else {
    die("No Internship ID provided.");
}

$final_avg = 0;
$both_assessed = isset($evals['Academic']) && isset($evals['Industry']);
if ($both_assessed) {
    $final_avg = ($evals['Academic']['total_mark'] + $evals['Industry']['total_mark']) / 2;
}

function score_class($v) {
    if ($v === null || $v === '') return '';
    if ($v >= 70) return 'score-hi';
    if ($v >= 50) return 'score-md';
    return 'score-lo';
}

include("header.php");
?>

<style>
    .report-wrap { max-width: 900px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 12px; }
    .report-page-header { display: flex; justify-content: space-between; border-bottom: 2px solid #1a5eb8; padding-bottom: 15px; margin-bottom: 25px; }
    .page-title-main { font-size: 1.6rem; font-weight: 700; color: #1e293b; margin: 0; }
    .report-status-badge { font-size: 0.75rem; padding: 4px 12px; border-radius: 99px; border: 1px solid #cbd5e1; color: #64748b; }
    .section-label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 20px 0 10px; }
    
    .info-cards-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }
    .info-card { border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; }
    .ic-label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; margin-bottom: 5px; }
    .ic-value { font-size: 0.95rem; font-weight: 600; color: #1e293b; }

    .result-strip { display: grid; grid-template-columns: 1fr auto; gap: 15px; margin-top: 20px; }
    .avg-bar-card { border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; flex-grow: 1; }
    .avg-bar-track { height: 8px; background: #e2e8f0; border-radius: 10px; margin: 10px 0; overflow: hidden; }
    .avg-bar-fill { height: 100%; background: #1a5eb8; border-radius: 10px; transition: width 0.5s; }
    
    .result-big-card { background: #1a5eb8; color: white; padding: 20px; border-radius: 10px; text-align: center; min-width: 140px; }
    .rbc-num { font-size: 2.5rem; font-weight: 800; line-height: 1; }
    .rbc-sub { font-size: 0.75rem; opacity: 0.8; }

    .score-table-wrap { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-top: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f8fafc; padding: 12px; font-size: 0.8rem; color: #64748b; text-align: center; border-bottom: 1px solid #e2e8f0; }
    th:first-child { text-align: left; }
    td { padding: 12px; text-align: center; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
    td:first-child { text-align: left; font-weight: 500; }

    .score-pill { display: inline-block; padding: 4px 12px; border-radius: 99px; font-size: 0.8rem; font-weight: 600; }
    .score-hi { background: #dcfce7; color: #166534; }
    .score-md { background: #fef9c3; color: #854d0e; }
    .score-lo { background: #fee2e2; color: #991b1b; }
    .total-row td { background: #f1f5f9 !important; font-weight: 800; color: #1a5eb8; }

    .col-type-badge { padding: 3px 10px; border-radius: 99px; font-size: 0.75rem; font-weight: 600; }
    .badge-acad { background: #dbeafe; color: #1e40af; }
    .badge-ind { background: #dcfce7; color: #166534; }

    .report-actions { display: flex; justify-content: flex-end; margin-top: 20px; }
    .btn-print { background: #1a5eb8; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 600; cursor: pointer; }
    .back-link { text-decoration: none; color: #64748b; font-size: 0.9rem; margin-bottom: 15px; display: inline-block; }
</style>

<div class="dashboard-container">
    <div class="report-wrap">
        <a href="<?php echo htmlspecialchars($dash_link); ?>" class="back-link">&larr; Back to Dashboard</a>

        <div class="report-page-header">
            <div>
                <h1 class="page-title-main">Final Performance Report</h1>
                <p class="sub">Internship ID: #<?php echo htmlspecialchars($info['internship_id']); ?></p>
            </div>
            <span class="report-status-badge"><?php echo $both_assessed ? 'Assessment Complete' : 'Assessment Pending'; ?></span>
        </div>

        <p class="section-label">Student & Placement</p>
        <div class="info-cards-grid">
            <div class="info-card"><div class="ic-label">Student Name</div><div class="ic-value"><?php echo htmlspecialchars($info['student_name']); ?></div></div>
            <div class="info-card"><div class="ic-label">Student ID</div><div class="ic-value"><?php echo htmlspecialchars($info['student_id']); ?></div></div>
            <div class="info-card"><div class="ic-label">Company</div><div class="ic-value"><?php echo htmlspecialchars($info['company_name']); ?></div></div>
        </div>

        <div class="result-strip">
            <div class="avg-bar-card">
                <div class="ab-label">Combined average score</div>
                <div class="avg-bar-track"><div class="avg-bar-fill" style="width: <?php echo $final_avg; ?>%"></div></div>
                <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:#64748b;"><span>0</span><span><?php echo number_format($final_avg, 1); ?> / 100</span><span>100</span></div>
            </div>
            <div class="result-big-card">
                <div class="rbc-num"><?php echo number_format($final_avg, 1); ?>%</div>
                <div class="rbc-sub">Overall Result</div>
            </div>
        </div>

        <p class="section-label">Score Breakdown</p>
        <div class="score-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Criteria</th>
                        <th><span class="col-type-badge badge-acad">Academic</span></th>
                        <th><span class="col-type-badge badge-ind">Industry</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $criteria = [
                        'score_tasks' => 'Tasks / Projects', 'score_safety' => 'Health & Safety',
                        'score_theory' => 'Theoretical Knowledge', 'score_presentation' => 'Presentation / Report',
                        'score_clarity' => 'Language Clarity', 'score_learning' => 'Lifelong Learning',
                        'score_project_mgmt' => 'Project Management', 'score_time_mgmt' => 'Time Management'
                    ];
                    foreach ($criteria as $col => $label):
                        $acad = $evals['Academic'][$col] ?? null; $ind = $evals['Industry'][$col] ?? null;
                    ?>
                    <tr>
                        <td><?php echo $label; ?></td>
                        <td><?php echo $acad !== null ? "<span class='score-pill ".score_class($acad)."'>".number_format($acad,2)."</span>" : "—"; ?></td>
                        <td><?php echo $ind !== null ? "<span class='score-pill ".score_class($ind)."'>".number_format($ind,2)."</span>" : "—"; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td>Total Mark (100%)</td>
                        <td><?php echo isset($evals['Academic']['total_mark']) ? number_format($evals['Academic']['total_mark'], 2) : '—'; ?></td>
                        <td><?php echo isset($evals['Industry']['total_mark']) ? number_format($evals['Industry']['total_mark'], 2) : '—'; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="report-actions">
            <button onclick="printReport()" class="btn-print">Print Report</button>
        </div>
    </div>
</div>

<script>
function printReport() {
    const content = document.querySelector('.report-wrap').innerHTML;
    const printFrame = document.createElement('iframe');
    printFrame.style.position = 'fixed'; printFrame.style.right = '0'; printFrame.style.bottom = '0';
    printFrame.style.width = '0'; printFrame.style.height = '0'; printFrame.style.border = '0';
    document.body.appendChild(printFrame);

    const doc = printFrame.contentWindow.document;
    doc.open();
    doc.write(`
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #1e293b; padding: 40px; }
                .back-link, .report-actions { display: none !important; }
                .report-page-header { display: flex; justify-content: space-between; border-bottom: 2px solid #1a5eb8; padding-bottom: 15px; margin-bottom: 25px; }
                .page-title-main { font-size: 1.6rem; font-weight: bold; margin: 0; }
                .info-cards-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px; }
                .info-card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
                .ic-label { font-size: 10px; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
                .ic-value { font-size: 14px; font-weight: bold; }
                .result-strip { display: grid; grid-template-columns: 1fr auto; gap: 15px; margin-bottom: 30px; }
                .avg-bar-card { border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; }
                .avg-bar-track { height: 8px; background: #e2e8f0; border-radius: 10px; margin: 10px 0; }
                .avg-bar-fill { height: 8px; background: #1a5eb8 !important; border-radius: 10px; -webkit-print-color-adjust: exact; }
                .result-big-card { background: #1a5eb8 !important; color: white !important; padding: 20px; border-radius: 10px; text-align: center; min-width: 120px; -webkit-print-color-adjust: exact; }
                .rbc-num { font-size: 32px; font-weight: 800; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: center; font-size: 12px; }
                th:first-child, td:first-child { text-align: left; }
                .score-pill { padding: 4px 10px; border-radius: 99px; font-weight: bold; -webkit-print-color-adjust: exact; }
                .score-hi { background: #dcfce7 !important; color: #166534 !important; }
                .score-lo { background: #fee2e2 !important; color: #991b1b !important; }
                .total-row td { background: #f1f5f9 !important; font-weight: bold; color: #1a5eb8 !important; -webkit-print-color-adjust: exact; }
                .badge-acad { background: #dbeafe !important; color: #1e40af !important; -webkit-print-color-adjust: exact; }
                .badge-ind { background: #dcfce7 !important; color: #166534 !important; -webkit-print-color-adjust: exact; }
            </style>
        </head>
        <body>${content}</body>
        </html>
    `);
    doc.close();

    setTimeout(() => {
        printFrame.contentWindow.focus();
        printFrame.contentWindow.print();
        document.body.removeChild(printFrame);
    }, 500);
}
</script>
<?php include("footer.php"); ?>