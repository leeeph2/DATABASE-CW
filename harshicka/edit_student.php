<?php
// ─── 1. Session & Security ────────────────────────────────────────────────────
session_start();
require("database.php");

// Allow large POST bodies for Base64-encoded cropped images (~5 MB image = ~7 MB base64)
@ini_set('post_max_size', '16M');
@ini_set('upload_max_filesize', '16M');

// Make ALL mysqli errors throw exceptions — no more silent failures
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$msg        = "";
$student_id = "";

// ─── 2. Get Student ID from URL ───────────────────────────────────────────────
if (isset($_GET['id'])) {
    $student_id = mysqli_real_escape_string($conn, $_GET['id']);
} else {
    header("Location: view_students.php");
    exit();
}

// ─── 3. Fetch current record BEFORE the POST handler ─────────────────────────
//        (needed so we can read the existing profile_picture filename)
$query        = "SELECT s.*, i.company_name
                 FROM students s
                 LEFT JOIN internships i ON s.student_id = i.student_id
                 WHERE s.student_id = '$student_id'";
$result       = mysqli_query($conn, $query);
$student_data = mysqli_fetch_assoc($result);

if (!$student_data) {
    header("Location: view_students.php");
    exit();
}

// ─── 4. Handle POST (save changes) ───────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $sname    = mysqli_real_escape_string($conn, $_POST['student_name']);
    $pid      = $_POST['programme_id'];
    $lect_id  = empty($_POST['lecturer_id'])   ? NULL : $_POST['lecturer_id'];
    $super_id = empty($_POST['supervisor_id']) ? NULL : $_POST['supervisor_id'];
    $company  = mysqli_real_escape_string($conn, $_POST['company_name']);

    // Start with the existing picture filename; replace only if a new file arrives
    $pic_filename = $student_data['profile_picture'] ?? 'default.png';

    mysqli_begin_transaction($conn);

    try {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        $has_cropped_data = !empty($_POST['cropped_image_data'])
                            && strpos($_POST['cropped_image_data'], 'data:image/') === 0;

        if ($has_cropped_data) {
            // Parse:  data:image/jpeg;base64,/9j/4AAQ...
            $data_uri  = $_POST['cropped_image_data'];
            $comma_pos = strpos($data_uri, ',');
            $meta      = substr($data_uri, 0, $comma_pos);          // data:image/jpeg;base64
            $b64       = substr($data_uri, $comma_pos + 1);
            $img_data  = base64_decode($b64);

            if ($img_data === false || strlen($img_data) === 0) {
                throw new Exception("Cropped image data was empty or corrupt. Please try again.");
            }
            if (strlen($img_data) > 5 * 1024 * 1024) {
                throw new Exception("Photo is too large after cropping. Maximum size is 5 MB.");
            }

            // Determine extension from the data URI mime type
            $ext = 'jpg'; // Cropper always outputs JPEG
            if (strpos($meta, 'image/png') !== false)  $ext = 'png';
            if (strpos($meta, 'image/webp') !== false) $ext = 'webp';

            // Delete old file (never delete default.png)
            $old_pic = $student_data['profile_picture'] ?? '';
            if (!empty($old_pic) && $old_pic !== 'default.png') {
                $old_path = $upload_dir . basename($old_pic);
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }

            // Build unique filename and write decoded bytes to disk
            $pic_filename = 'student_'
                . preg_replace('/[^a-z0-9]/i', '', $student_id)
                . '_' . time() . '.' . $ext;

            if (file_put_contents($upload_dir . $pic_filename, $img_data) === false) {
                throw new Exception(
                    "Could not save photo to <code>" . htmlspecialchars($upload_dir . $pic_filename) . "</code>. " .
                    "Check that <strong>uploads/</strong> is writable."
                );
            }

        } elseif (isset($_FILES['profile_picture']) &&
                  $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {

            // Fallback: plain file upload (no cropper used)
            $file          = $_FILES['profile_picture'];
            $detected_mime = mime_content_type($file['tmp_name']);
            $mime_to_ext   = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];

            if (!isset($mime_to_ext[$detected_mime])) {
                throw new Exception("Unsupported image type: " . htmlspecialchars($detected_mime));
            }
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception("Photo is too large. Maximum size is 5 MB.");
            }

            $ext     = $mime_to_ext[$detected_mime];
            $old_pic = $student_data['profile_picture'] ?? '';
            if (!empty($old_pic) && $old_pic !== 'default.png') {
                $old_path = $upload_dir . basename($old_pic);
                if (file_exists($old_path)) unlink($old_path);
            }

            $pic_filename = 'student_'
                . preg_replace('/[^a-z0-9]/i', '', $student_id)
                . '_' . time() . '.' . $ext;

            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $pic_filename)) {
                throw new Exception("Could not save photo. Check that <strong>uploads/</strong> is writable.");
            }
        }

        // ── 4b. Update students (name + programme + staff + photo in ONE statement) ──
        $stmt_s = mysqli_prepare($conn,
            "UPDATE students
             SET student_name=?, programme_id=?, lecturer_id=?, supervisor_id=?, profile_picture=?
             WHERE student_id=?"
        );
        mysqli_stmt_bind_param($stmt_s, "ssssss",
            $sname, $pid, $lect_id, $super_id, $pic_filename, $student_id
        );
        mysqli_stmt_execute($stmt_s);

        // ── 4c. Update internships ────────────────────────────────────────────
        $stmt_i = mysqli_prepare($conn,
            "UPDATE internships SET company_name=? WHERE student_id=?"
        );
        mysqli_stmt_bind_param($stmt_i, "ss", $company, $student_id);
        mysqli_stmt_execute($stmt_i);

        // ── 4d. Sync users table (login display name) ─────────────────────────
        $stmt_u = mysqli_prepare($conn,
            "UPDATE users SET full_name=? WHERE user_id=?"
        );
        mysqli_stmt_bind_param($stmt_u, "ss", $sname, $student_id);
        mysqli_stmt_execute($stmt_u);

        mysqli_commit($conn);
        header("Location: edit_student.php?id=$student_id&msg=updated&name=" . urlencode($sname));
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $msg = "<div style='padding:15px; background:#fef2f2; color:#b91c1c;
                             border-radius:8px; margin-bottom:20px;'>
                    <strong>ERROR:</strong> " . $e->getMessage() . "
                </div>";
    }

    // Re-fetch so the form shows current DB values after a failed save
    $result       = mysqli_query($conn, $query);
    $student_data = mysqli_fetch_assoc($result);
}

// ─── 5. Fetch dropdown data ───────────────────────────────────────────────────
$prog_result    = mysqli_query($conn, "SELECT * FROM programmes");
$lect_result    = mysqli_query($conn,
    "SELECT user_id, full_name FROM users WHERE role = 'Lecturer' ORDER BY full_name ASC");
$super_result   = mysqli_query($conn,
    "SELECT user_id, full_name FROM users WHERE role = 'Supervisor' ORDER BY full_name ASC");
$company_result = mysqli_query($conn,
    "SELECT DISTINCT company_name FROM internships WHERE company_name != '' ORDER BY company_name ASC");

// ─── 6. Build photo src for the template ─────────────────────────────────────
$db_pic     = $student_data['profile_picture'] ?? 'default.png';
$has_db_pic = !empty($db_pic);
$photo_src  = 'uploads/' . htmlspecialchars($db_pic);

include("header.php");
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">

<style>
.select2-container .select2-selection--single {
    height: 42px; border: 1px solid #d1d5db;
    border-radius: 8px; display: flex; align-items: center;
}
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #1f2937; font-size: 0.95rem;
}
</style>

<div class="edit-page-wrap">
    <div class="page-header-flex">
        <div>
            <span class="stat-label">Registry Action</span>
            <h1 class="page-title-main">Edit Student Profile</h1>
            <a href="view_students.php" class="back-link">&larr; Back to Registry</a>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
        <div class="edit-notice success" style="margin-bottom:20px;">
            <span class="notice-icon">✓</span>
            <div>Record <strong><?php echo htmlspecialchars(urldecode($_GET['name'] ?? '')); ?></strong> successfully updated.</div>
        </div>
    <?php endif; ?>

    <?php echo $msg; ?>

    <div class="edit-form-card">
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <div class="form-section-header"><h2>Student Identity & Photo</h2></div>

            <div class="form-body">
                <div class="aa-form-group" style="margin-bottom:30px;">
                    <label>Profile Photo</label>
                    <div class="photo-zone" id="photoZone"
                         style="width:300px; height:250px; margin:0; display:flex; flex-direction:column;
                                align-items:center; justify-content:center; position:relative; overflow:hidden;">

                        <img id="photoPreview" class="preview"
                             src="<?php echo $photo_src; ?>"
                             style="display:block;"
                             onerror="this.style.display='none'; document.getElementById('photoPlaceholder').style.display='flex'; this.onerror=null;">

                        <div id="photoPlaceholder"
                             style="display:none; flex-direction:column; align-items:center; justify-content:center; gap:6px;">
                            <div class="photo-zone-title">No photo set</div>
                            <div class="photo-zone-sub">JPG, PNG or WEBP · Max 5 MB</div>
                        </div>

                        <!-- Click to upload link -->
                        <div onclick="document.getElementById('photoInput').click()"
                             style="position:absolute; bottom:0; left:0; width:100%;
                                    text-align:center; padding:8px 0; font-size:0.82rem;
                                    color:#6366f1; cursor:pointer; text-decoration:underline;
                                    background:rgba(255,255,255,0.85);"
                             onmouseenter="this.style.color='#4338ca'"
                             onmouseleave="this.style.color='#6366f1'">
                            Click here to upload image
                        </div>

                        <input type="file" name="profile_picture" id="photoInput"
                               accept="image/png,image/jpeg,image/webp" style="display:none;">
                    </div>

                    <div id="photoError"
                         style="display:none; color:#b91c1c; font-size:0.85rem; margin-top:8px;"></div>
                </div>

                <div class="form-grid-2">
                    <div class="fg">
                        <label>Student ID</label>
                        <input type="text"
                               value="<?php echo htmlspecialchars($student_data['student_id']); ?>"
                               readonly style="background:#f9fafb;">
                        <span class="field-hint">Primary ID cannot be changed.</span>
                    </div>
                    <div class="fg">
                        <label>Full Name</label>
                        <input type="text" name="student_name" required
                               value="<?php echo htmlspecialchars($student_data['student_name']); ?>">
                    </div>
                    <div class="fg span-2">
                        <label>Academic Programme</label>
                        <select name="programme_id" class="search-select" required>
                            <?php mysqli_data_seek($prog_result, 0);
                            while ($row = mysqli_fetch_assoc($prog_result)): ?>
                                <option value="<?php echo $row['programme_id']; ?>"
                                    <?php if ($student_data['programme_id'] == $row['programme_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['programme_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="form-section-header"><h2>Supervision & Placement</h2></div>
            <div class="form-body">
                <div class="form-grid-2">

                    <div class="fg">
                        <label>Assigned Lecturer</label>
                        <select name="lecturer_id" class="search-select">
                            <option value="" <?php echo empty($student_data['lecturer_id']) ? 'selected disabled' : ''; ?>>-- Unassigned --</option>
                            <?php mysqli_data_seek($lect_result, 0);
                            while ($row = mysqli_fetch_assoc($lect_result)): ?>
                                <option value="<?php echo $row['user_id']; ?>"
                                    <?php if (($student_data['lecturer_id'] ?? '') == $row['user_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['full_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="fg">
                        <label>Assigned Supervisor</label>
                        <select name="supervisor_id" class="search-select">
                            <option value="" <?php echo empty($student_data['supervisor_id']) ? 'selected disabled' : ''; ?>>-- Unassigned --</option>
                            <?php mysqli_data_seek($super_result, 0);
                            while ($row = mysqli_fetch_assoc($super_result)): ?>
                                <option value="<?php echo $row['user_id']; ?>"
                                    <?php if (($student_data['supervisor_id'] ?? '') == $row['user_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['full_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="fg span-2">
                        <label>Internship Company</label>
                        <select name="company_name" class="search-select">
                            <option value="" <?php echo empty($student_data['company_name']) ? 'selected disabled' : ''; ?>>-- Pending Placement --</option>
                            <?php mysqli_data_seek($company_result, 0);
                            while ($row = mysqli_fetch_assoc($company_result)): ?>
                                <option value="<?php echo htmlspecialchars($row['company_name']); ?>"
                                    <?php if (($student_data['company_name'] ?? '') == $row['company_name']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['company_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                </div>
            </div>

            <div class="form-footer" style="display:flex; justify-content:flex-end; padding:20px 36px;">
                <div class="footer-actions" style="display:flex; gap:12px;">
                    <a href="view_students.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-save">Save Updates</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="cropModal" class="crop-modal">
    <div class="crop-modal-content">
        <h3>Adjust Profile Photo</h3>
        <div class="crop-container">
            <img id="imageToCrop" src="" style="max-width:100%;">
        </div>
        <div class="crop-actions">
            <button type="button" class="btn-cancel" onclick="closeCropModal()">Cancel</button>
            <button type="button" class="btn-save" id="btnCropConfirm">Confirm & Save</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="script.js?v=<?php echo time(); ?>"></script>
<?php include("footer.php"); ?>