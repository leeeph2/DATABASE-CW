<?php
// ============================================================
// STEP 1: Session, DB, Auth
// ============================================================
session_start();
require("database.php");

// Enable mysqli exceptions so try/catch ACTUALLY catches DB errors
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

// ============================================================
// STEP 2: Fetch Dropdown Data
// ============================================================
$prog_result    = mysqli_query($conn, "SELECT * FROM programmes");
$lect_result    = mysqli_query($conn, "SELECT user_id, full_name FROM users WHERE role = 'Assessor'");
$company_result = mysqli_query($conn, "SELECT DISTINCT company_name FROM internships WHERE company_name != '' ORDER BY company_name ASC");

// ============================================================
// STEP 3: Handle POST
// ============================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3a. Read inputs (no manual escaping needed — prepared statements handle it)
    $sid     = strtoupper(trim($_POST['student_id']   ?? ''));
    $sname   = trim($_POST['student_name']  ?? '');
    $pid     = trim($_POST['programme_id']  ?? '');
    $lid     = trim($_POST['supervisor_id'] ?? '');
    $company = trim($_POST['company_name']  ?? '');

    if (!$sid || !$sname || !$pid || !$lid || !$company) {
        header("Location: add_student.php?error=missing_fields");
        exit();
    }

    $default_pass = password_hash("student123", PASSWORD_DEFAULT);

    // ---- 3b. Photo Upload ----
    $profile_pic = "default.png";

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {

        $upload_error = $_FILES['profile_picture']['error'];

        if ($upload_error !== UPLOAD_ERR_OK) {
            $upload_err_map = [
                UPLOAD_ERR_INI_SIZE   => 'upload_too_large',
                UPLOAD_ERR_FORM_SIZE  => 'upload_too_large',
                UPLOAD_ERR_PARTIAL    => 'upload_partial',
                UPLOAD_ERR_NO_TMP_DIR => 'upload_no_tmp',
                UPLOAD_ERR_CANT_WRITE => 'upload_cant_write',
                UPLOAD_ERR_EXTENSION  => 'upload_blocked',
            ];
            $err_code = $upload_err_map[$upload_error] ?? 'upload_failed';
            header("Location: add_student.php?error=$err_code");
            exit();
        }

        // Validate by MIME type (not just extension)
        $allowed_ext = ['jpg', 'jpeg', 'png'];
$file_ext    = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));

if (!in_array($file_ext, $allowed_ext)) {
    header("Location: add_student.php?error=invalid_image_type");
    exit();
}

        // Enforce 2 MB limit
        if ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
            header("Location: add_student.php?error=upload_too_large");
            exit();
        }

        // Use __DIR__ so the path is always absolute and correct
        $upload_dir = __DIR__ . '/uploads/';

        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                header("Location: add_student.php?error=upload_dir_failed");
                exit();
            }
        }

        if (!is_writable($upload_dir)) {
            header("Location: add_student.php?error=upload_not_writable");
            exit();
        }

        $new_file_name = $sid . '_' . time() . '.' . $file_ext;
        $dest_path     = $upload_dir . $new_file_name;

        if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dest_path)) {
            header("Location: add_student.php?error=move_failed");
            exit();
        }

        $profile_pic = $new_file_name;
    }

    // ---- 3c. Duplicate checks ----
    $check_stmt = mysqli_prepare($conn, "SELECT student_id FROM students WHERE student_id = ?");
    mysqli_stmt_bind_param($check_stmt, "s", $sid);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    $is_dup = mysqli_stmt_num_rows($check_stmt) > 0;
    mysqli_stmt_close($check_stmt);

    if ($is_dup) {
        header("Location: add_student.php?error=duplicate");
        exit();
    }

    $check_user = mysqli_prepare($conn, "SELECT user_id FROM users WHERE user_id = ?");
    mysqli_stmt_bind_param($check_user, "s", $sid);
    mysqli_stmt_execute($check_user);
    mysqli_stmt_store_result($check_user);
    $user_exists = mysqli_stmt_num_rows($check_user) > 0;
    mysqli_stmt_close($check_user);

    if ($user_exists) {
        header("Location: add_student.php?error=duplicate");
        exit();
    }

    // ---- 3d. Transaction ----
    mysqli_begin_transaction($conn);
    try {
        // A. users table
        $stmt_u = mysqli_prepare($conn, "INSERT INTO users (user_id, username, password, full_name, role) VALUES (?, ?, ?, ?, 'Student')");
        mysqli_stmt_bind_param($stmt_u, "ssss", $sid, $sid, $default_pass, $sname);
        mysqli_stmt_execute($stmt_u);
        mysqli_stmt_close($stmt_u);

        // B. students table
        $stmt_s = mysqli_prepare($conn, "INSERT INTO students (student_id, student_name, programme_id, supervisor_id, profile_picture) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_s, "sssss", $sid, $sname, $pid, $lid, $profile_pic);
        mysqli_stmt_execute($stmt_s);
        mysqli_stmt_close($stmt_s);

        // C. internships table
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
        error_log("add_student ERROR: " . $e->getMessage());
        // Shows DB detail in URL — remove &detail= line in production
        header("Location: add_student.php?error=create_failed&detail=" . urlencode($e->getMessage()));
        exit();
    }
}

include("header.php");
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="page-header-flex">
    <div>
        <span class="stat-label">Registry Action</span>
        <h1 class="page-title-main">Register New Student</h1>
        <a href="view_students.php" class="back-link">&larr; Back to Registry</a>
    </div>
</div>

<?php
// ---- Error / success banner ----
if (isset($_GET['error'])) {
    $err_messages = [
        'duplicate'           => 'A student with this ID already exists.',
        'missing_fields'      => 'All fields are required.',
        'invalid_image_type'  => 'Invalid file. Only JPG and PNG are allowed.',
        'upload_too_large'    => 'Image exceeds the 2 MB limit.',
        'upload_partial'      => 'Upload was interrupted — please try again.',
        'upload_no_tmp'       => 'Server error: no temp folder. Contact your administrator.',
        'upload_cant_write'   => 'Server error: disk write failed. Contact your administrator.',
        'upload_not_writable' => 'Server error: uploads/ folder is not writable. Run: chmod 755 uploads/',
        'upload_dir_failed'   => 'Server error: could not create uploads/ folder.',
        'move_failed'         => 'Could not save the uploaded image. Check folder permissions.',
        'create_failed'       => 'Database error while creating the record. See detail below.',
    ];
    $msg    = htmlspecialchars($err_messages[$_GET['error']] ?? 'An unexpected error occurred.');
    $detail = isset($_GET['detail'])
        ? '<br><small style="opacity:.75">DB detail: ' . htmlspecialchars($_GET['detail']) . '</small>'
        : '';
    echo '<div style="background:#fee2e2;border:1px solid #f87171;color:#991b1b;
                      padding:14px 20px;border-radius:8px;margin-bottom:20px;">
            <strong>Error:</strong> ' . $msg . $detail . '
          </div>';
}
?>

<div class="stat-card form-card-wide">
    <p class="stat-card-desc" style="margin-bottom:30px; border-bottom:1px solid var(--border-color); padding-bottom:20px;">
        Complete this form to generate a system account, academic profile, and pending internship record for the candidate.
    </p>

    <form method="POST" autocomplete="off" enctype="multipart/form-data" onsubmit="return validateAndSubmit()">

        <div class="form-grid-2col">

            <!-- Photo upload -->
            <div class="photo-upload-container">
                <label class="photo-upload-box" id="drop-zone" for="file-upload">
                    <img id="image-preview" class="photo-preview" src="#" alt="Preview"
                         style="display:none; width:100%; height:100%; object-fit:cover; border-radius:8px;">
                    <div id="upload-placeholder">
                        <div class="photo-upload-label">Upload Profile Photo</div>
                        <div class="photo-upload-subtext">JPG or PNG &middot; Max 2 MB</div>
                    </div>
                    <input type="file" name="profile_picture" id="file-upload"
                           accept="image/png, image/jpeg"
                           style="display:none;"
                           onchange="previewImage(event)">
                </label>
                <p id="file-error" style="color:#dc2626; font-size:0.8rem; margin-top:6px; display:none;"></p>
            </div>

            <!-- Form fields -->
            <div>
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" required placeholder="e.g. 20715097" pattern="[A-Za-z0-9\-]{3,20}">
                </div>

                <div class="form-group">
                    <label>Full Student Name</label>
                    <input type="text" name="student_name" required placeholder="e.g. Phoon Le-Ee">
                </div>

                <div class="form-group">
                    <label>Academic Programme</label>
                    <select name="programme_id" class="form-control search-select" required>
                        <option value="">-- Select Programme --</option>
                        <?php mysqli_data_seek($prog_result, 0); while ($row = mysqli_fetch_assoc($prog_result)): ?>
                            <option value="<?= htmlspecialchars($row['programme_id']) ?>">
                                <?= htmlspecialchars($row['programme_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Assign Assessor</label>
                    <select name="supervisor_id" class="form-control search-select" required>
                        <option value="">-- Select Lecturer --</option>
                        <?php mysqli_data_seek($lect_result, 0); while ($row = mysqli_fetch_assoc($lect_result)): ?>
                            <option value="<?= htmlspecialchars($row['user_id']) ?>">
                                <?= htmlspecialchars($row['full_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:30px;">
                    <label>Internship Company</label>
                    <select name="company_name" class="form-control search-select" required>
                        <option value="">-- Select Company --</option>
                        <?php mysqli_data_seek($company_result, 0); while ($row = mysqli_fetch_assoc($company_result)): ?>
                            <option value="<?= htmlspecialchars($row['company_name']) ?>">
                                <?= htmlspecialchars($row['company_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" id="submitBtn" class="btn-primary"
                        style="width:100%; padding:14px; font-size:0.95rem;">
                    Create Student Record &amp; Account
                </button>
            </div>

        </div>
    </form>
</div>

<script>
function previewImage(event) {
    const file    = event.target.files[0];
    const errEl   = document.getElementById('file-error');
    const preview = document.getElementById('image-preview');
    const holder  = document.getElementById('upload-placeholder');

    errEl.style.display = 'none';
    errEl.textContent   = '';

    if (!file) return;

    if (!['image/jpeg', 'image/png'].includes(file.type)) {
        errEl.textContent   = 'Only JPG or PNG files are accepted.';
        errEl.style.display = 'block';
        event.target.value  = '';
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        errEl.textContent   = 'Image must be under 2 MB.';
        errEl.style.display = 'block';
        event.target.value  = '';
        return;
    }

    const reader  = new FileReader();
    reader.onload = e => {
        preview.src           = e.target.result;
        preview.style.display = 'block';
        holder.style.display  = 'none';
    };
    reader.readAsDataURL(file);
}

function validateAndSubmit() {
    const btn = document.getElementById('submitBtn');
    btn.disabled    = true;
    btn.textContent = 'Creating… please wait';
    return true;
}

$(document).ready(function () {
    $('.search-select').select2({ width: '100%' });
});
</script>

<script src="script.js"></script>
<?php include("footer.php"); ?>