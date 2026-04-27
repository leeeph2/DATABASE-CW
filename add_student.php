<?php
// ============================================================
// STEP 1: Session, DB, Auth
// ============================================================
session_start();
require("database.php");

@ini_set('post_max_size', '16M');
@ini_set('upload_max_filesize', '16M');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

// ============================================================
// STEP 2: Fetch Dropdown Data
// ============================================================
$prog_result    = mysqli_query($conn, "SELECT * FROM programmes");
$lect_result    = mysqli_query($conn, "SELECT user_id, full_name FROM users WHERE role = 'Lecturer' ORDER BY full_name ASC");
$super_result   = mysqli_query($conn, "SELECT user_id, full_name FROM users WHERE role = 'Supervisor' ORDER BY full_name ASC");
$company_result = mysqli_query($conn, "SELECT DISTINCT company_name FROM internships WHERE company_name != '' ORDER BY company_name ASC");

// ============================================================
// STEP 3: Handle POST
// ============================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $sid     = strtoupper(trim($_POST['student_id']   ?? ''));
    $sname   = trim($_POST['student_name']  ?? '');
    $pid     = trim($_POST['programme_id']  ?? '');
    $company = trim($_POST['company_name']  ?? '');
    
    $lect_id = empty($_POST['lecturer_id']) ? NULL : trim($_POST['lecturer_id']);
    $super_id = empty($_POST['supervisor_id']) ? NULL : trim($_POST['supervisor_id']);

    if (!$sid || !$sname || !$pid) {
        header("Location: add_student.php?error=missing_fields");
        exit();
    }

    $default_pass = password_hash("student123", PASSWORD_DEFAULT);
    
    // --- PROFILE PICTURE UPLOAD LOGIC ---
    $profile_pic = "default.png";
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    if (empty($_POST['remove_photo']) || $_POST['remove_photo'] !== '1') {
        if (!empty($_POST['cropped_image_data']) && strpos($_POST['cropped_image_data'], 'data:image/') === 0) {
            $data = explode(',', $_POST['cropped_image_data']);
            $img_data = base64_decode($data[1]);
            $new_file_name = preg_replace('/[^A-Za-z0-9\-]/', '', $sid) . '_' . time() . '.jpg';
            if (file_put_contents($upload_dir . $new_file_name, $img_data)) {
                $profile_pic = $new_file_name;
            }
        }
    }
    // -----------------------------------------

    $check_stmt = mysqli_prepare($conn, "SELECT student_id FROM students WHERE student_id = ?");
    mysqli_stmt_bind_param($check_stmt, "s", $sid);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    $is_dup = mysqli_stmt_num_rows($check_stmt) > 0;
    mysqli_stmt_close($check_stmt);
    if ($is_dup) { header("Location: add_student.php?error=duplicate"); exit(); }

    $check_user = mysqli_prepare($conn, "SELECT user_id FROM users WHERE user_id = ?");
    mysqli_stmt_bind_param($check_user, "s", $sid);
    mysqli_stmt_execute($check_user);
    mysqli_stmt_store_result($check_user);
    $user_exists = mysqli_stmt_num_rows($check_user) > 0;
    mysqli_stmt_close($check_user);
    if ($user_exists) { header("Location: add_student.php?error=duplicate"); exit(); }

    mysqli_begin_transaction($conn);
    try {
        $stmt_u = mysqli_prepare($conn, "INSERT INTO users (user_id, username, password, full_name, role, profile_picture) VALUES (?, ?, ?, ?, 'Student', ?)");
        mysqli_stmt_bind_param($stmt_u, "sssss", $sid, $sid, $default_pass, $sname, $profile_pic);
        mysqli_stmt_execute($stmt_u);
        mysqli_stmt_close($stmt_u);

        $stmt_s = mysqli_prepare($conn, "INSERT INTO students (student_id, student_name, programme_id, lecturer_id, supervisor_id, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_s, "ssssss", $sid, $sname, $pid, $lect_id, $super_id, $profile_pic);
        mysqli_stmt_execute($stmt_s);
        mysqli_stmt_close($stmt_s);

        $int_id = "INT-" . strtoupper(substr($sid, -4)) . "-" . time();
        $stmt_i = mysqli_prepare($conn, "INSERT INTO internships (internship_id, student_id, company_name, internship_status) VALUES (?, ?, ?, 'Pending')");
        mysqli_stmt_bind_param($stmt_i, "sss", $int_id, $sid, $company);
        mysqli_stmt_execute($stmt_i);
        mysqli_stmt_close($stmt_i);

        mysqli_commit($conn);
        header("Location: view_students.php?msg=added&name=" . urlencode($sname));
        exit();
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        header("Location: add_student.php?error=create_failed&detail=" . urlencode($e->getMessage()));
        exit();
    }
}

include("header.php");
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">


<div class="page-header-flex">
    <div>
        <span class="stat-label">Registry Action</span>
        <h1 class="page-title-main">Register New Student</h1>
        <a href="view_students.php" class="back-link">&larr; Back to Registry</a>
    </div>
</div>

<?php if (isset($_GET['error'])):
    $err_messages = [
        'duplicate'      => 'A student with this ID already exists.',
        'missing_fields' => 'All required fields must be filled.',
        'create_failed'  => 'Database error while creating the record.',
    ];
    $emsg   = htmlspecialchars($err_messages[$_GET['error']] ?? 'An unexpected error occurred.');
    $detail = isset($_GET['detail']) ? '<br><small>Detail: ' . htmlspecialchars($_GET['detail']) . '</small>' : '';
?>
<div class="error-banner"><strong>Error:</strong> <?= $emsg . $detail ?></div>
<?php endif; ?>

<div class="wizard-shell">

    <aside class="wizard-sidebar">
        <div class="sidebar-card">
            <p class="sidebar-title">Registration Steps</p>

            <ul class="step-list" id="stepList">
                <li class="step-item active" data-step="1" onclick="goToStep(1)">
                    <div class="step-dot">1</div>
                    <div class="step-meta"><div class="step-label">Identity</div><div class="step-sublabel">ID & full name</div></div>
                </li>
                <li class="step-item" data-step="2" onclick="goToStep(2)">
                    <div class="step-dot">2</div>
                    <div class="step-meta"><div class="step-label">Academic</div><div class="step-sublabel">Programme & lecturer</div></div>
                </li>
                <li class="step-item" data-step="3" onclick="goToStep(3)">
                    <div class="step-dot">3</div>
                    <div class="step-meta"><div class="step-label">Internship</div><div class="step-sublabel">Company placement</div></div>
                </li>
                <li class="step-item" data-step="4" onclick="goToStep(4)">
                    <div class="step-dot">4</div>
                    <div class="step-meta"><div class="step-label">Review</div><div class="step-sublabel">Confirm & submit</div></div>
                </li>
            </ul>

            <div class="progress-wrap">
                <div class="progress-label"><span>Progress</span><span id="progressPct">25%</span></div>
                <div class="progress-bar-bg"><div class="progress-bar-fill" id="progressBar" style="width:25%"></div></div>
            </div>
        </div>
    </aside>

    <main class="wizard-main">
        <form method="POST" autocomplete="off" enctype="multipart/form-data" id="wizardForm" onsubmit="return handleSubmit()">

            <div class="form-panel active" id="panel1">
                <p class="panel-eyebrow">Step 1 of 4</p>
                <h2 class="panel-title">Student Identity</h2>
                <p class="panel-desc">Enter the student's ID number, full name, and an optional profile photo.</p>

                <div class="form-group form-group-photo">
                    <label>Profile Photo <span class="optional-label">(optional)</span></label>
                    <div class="photo-zone-container" id="photoZone">
                        <img id="photoPreview" class="preview-full hidden-element" src="#"
                             onerror="this.classList.add('hidden-element'); document.getElementById('photoPlaceholder').classList.remove('hidden-element'); document.getElementById('btnRemovePhoto').classList.add('hidden-element'); this.onerror=null;">

                        <div id="photoPlaceholder" class="photo-placeholder">
                            <div id="letterAvatar" class="letter-avatar">?</div>
                            <div class="photo-zone-sub">JPG, PNG or WEBP · Max 5 MB</div>
                        </div>

                        <div class="photo-links-bar">
                            <span class="photo-upload-link" onclick="document.getElementById('photoInput').click()">Click to upload image</span>
                            <span id="btnRemovePhoto" class="photo-remove-link hidden-element" onclick="window.removeProfilePhoto()">Remove Photo</span>
                        </div>

                        <input type="file" name="profile_picture" id="photoInput" class="hidden-element" accept="image/*">
                        <input type="hidden" name="remove_photo" id="removePhotoFlag" value="0">
                    </div>
                    <div id="photoError" class="photo-error-msg"></div>
                </div>

                <div class="field-row">
                    <div class="form-group">
                        <label>Student ID <span class="required-star">*</span></label>
                        <input type="text" name="student_id" id="f_sid" required placeholder="e.g. 20715097" pattern="[0-9]{3,20}" inputmode="numeric">
                    </div>
                    <div class="form-group">
                        <label>Full Name <span class="required-star">*</span></label>
                        <input type="text" name="student_name" id="f_sname" required placeholder="e.g. Phoon Le-Ee">
                    </div>
                </div>

                <div class="form-group">
                    <label>Default Password</label>
                    <input type="text" value="student123" disabled style="background:#f9fafb; color: var(--text-muted);">
                    <span class="auto-badge">✓ Auto-assigned · Student can change after login</span>
                </div>

                <div class="panel-nav"><span></span><button type="button" class="btn-next" onclick="nextStep(1)">Next: Academic Info <span>→</span></button></div>
            </div>

            <div class="form-panel" id="panel2">
                <p class="panel-eyebrow">Step 2 of 4</p>
                <h2 class="panel-title">Academic Details</h2>

                <div class="form-group">
                    <label>Academic Programme <span class="required-star">*</span></label>
                    <select name="programme_id" id="f_prog" class="search-select" required>
                        <option value="" disabled selected>— Select Programme —</option>
                        <?php mysqli_data_seek($prog_result, 0); while ($row = mysqli_fetch_assoc($prog_result)): ?>
                            <option value="<?= htmlspecialchars($row['programme_id']) ?>"><?= htmlspecialchars($row['programme_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="field-row">
                    <div class="form-group">
                        <label>Assigned Lecturer <span class="required-star">*</span></label>
                        <select name="lecturer_id" id="f_lect" class="search-select" required>
                            <option value="" disabled selected>— Select Lecturer —</option>
                            <?php mysqli_data_seek($lect_result, 0); while ($row = mysqli_fetch_assoc($lect_result)): ?>
                                <option value="<?= htmlspecialchars($row['user_id']) ?>"><?= htmlspecialchars($row['full_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <p class="field-hint">Evaluates this student's internship.</p>
                    </div>

                    <div class="form-group">
                        <label>Assigned Supervisor <span style="font-weight:400; color:var(--text-muted);">(optional)</span></label>
                        <select name="supervisor_id" id="f_super" class="search-select">
                            <option value="" selected>— Pending Supervisor —</option>
                            <?php mysqli_data_seek($super_result, 0); while ($row = mysqli_fetch_assoc($super_result)): ?>
                                <option value="<?= htmlspecialchars($row['user_id']) ?>"><?= htmlspecialchars($row['full_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <p class="field-hint">Can be assigned later if pending.</p>
                    </div>
                </div>

                <div class="panel-nav"><button type="button" class="btn-back" onclick="prevStep(2)">← Back</button><button type="button" class="btn-next" onclick="nextStep(2)">Next: Internship <span>→</span></button></div>
            </div>

           <div class="form-panel" id="panel3">
                <p class="panel-eyebrow">Step 3 of 4</p>
                <h2 class="panel-title">Internship Placement</h2>
                <p class="panel-desc">Select the company where the student is completing their internship.</p>

                <div class="form-group">
                    <label>Internship Company <span style="font-weight:400; color:var(--text-muted);">(optional)</span></label>
                    <select name="company_name" id="f_company" class="search-select">
                        <option value="" selected>— Pending Placement —</option>
                        <?php 
                        if(isset($company_result)) {
                            mysqli_data_seek($company_result, 0); 
                            while ($row = mysqli_fetch_assoc($company_result)): 
                        ?>
                            <option value="<?= htmlspecialchars($row['company_name']) ?>"><?= htmlspecialchars($row['company_name']) ?></option>
                        <?php endwhile; } ?>
                    </select>
                    <p class="field-hint">Leave as pending if the student hasn't secured a placement yet.</p>
                </div>

                <div class="panel-nav"><button type="button" class="btn-back" onclick="prevStep(3)">← Back</button><button type="button" class="btn-next" onclick="nextStep(3)">Review Record <span>→</span></button></div>
            </div>

            <div class="form-panel" id="panel4">
                <p class="panel-eyebrow">Step 4 of 4</p>
                <h2 class="panel-title">Review & Confirm</h2>
                <p class="panel-desc">Double-check all details before creating the student record. Three database entries will be created simultaneously.</p>

                <div class="review-photo-row">
                    <img id="reviewPhoto" class="review-avatar" src="#" alt="" style="display:none;">
                    <div id="reviewAvatarPlaceholder" class="review-avatar" style="display:flex; align-items:center; justify-content:center; background:#e0e7ff; font-size:1.6rem; font-weight:700; color:#4f46e5; border-radius:50%; width:64px; height:64px; user-select:none;">?</div>
                    <div>
                        <div class="review-name" id="rev_name">—</div>
                        <div class="review-sub" id="rev_id">—</div>
                    </div>
                </div>

                <div class="review-grid">
                    <div class="review-item"><div class="review-label">Student ID</div><div class="review-val" id="rev_sid">—</div></div>
                    <div class="review-item"><div class="review-label">Login Username</div><div class="review-val" id="rev_user">—</div></div>
                    <div class="review-item"><div class="review-label">Programme</div><div class="review-val" id="rev_prog">—</div></div>
                    <div class="review-item"><div class="review-label">Lecturer</div><div class="review-val" id="rev_lect">—</div></div>
                    <div class="review-item"><div class="review-label">Supervisor</div><div class="review-val" id="rev_super">—</div></div>
                    <div class="review-item"><div class="review-label">Company</div><div class="review-val" id="rev_company">—</div></div>
                    <div class="review-item"><div class="review-label">Internship Status</div><div class="review-val pending">Pending</div></div>
                    <div class="review-item"><div class="review-label">Default Password</div><div class="review-val">student123</div></div>
                </div>

                <div class="panel-nav"><button type="button" class="btn-back" onclick="prevStep(4)">← Edit Details</button><button type="submit" class="btn-next" id="submitBtn">✓ Create Student Record</button></div>
            </div>
        </form>
    </main>
</div>

<div id="cropModal" class="crop-modal">
    <div class="crop-modal-content">
        <h3 style="margin-top:0; margin-bottom:15px; color:var(--primary-dark);">Adjust Profile Photo</h3>
        <div class="crop-container"><img id="imageToCrop" src="" style="max-width: 100%; display: block;"></div>
        <div class="crop-actions">
            <button type="button" class="btn-cancel" onclick="closeCropModal()">Cancel</button>
            <button type="button" class="btn-save" id="btnCropConfirm">Confirm & Save</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="script.js?v=<?php echo time(); ?>"></script>

<?php include("footer.php"); ?>