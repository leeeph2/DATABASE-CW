<?php
session_start();
require("database.php");

@ini_set('post_max_size', '16M');
@ini_set('upload_max_filesize', '16M');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

$msg = "";
if (!isset($_GET['id'])) {
    header("Location: view_students.php");
    exit();
}
$student_id = mysqli_real_escape_string($conn, $_GET['id']);

// FETCH CURRENT RECORD
$query = "SELECT s.*, i.company_name FROM students s LEFT JOIN internships i ON s.student_id = i.student_id WHERE s.student_id = '$student_id'";
$result = mysqli_query($conn, $query);
$student_data = mysqli_fetch_assoc($result);

if (!$student_data) {
    header("Location: view_students.php");
    exit();
}

// HANDLE POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sname    = mysqli_real_escape_string($conn, $_POST['student_name']);
    $pid      = $_POST['programme_id'];
    $lect_id  = empty($_POST['lecturer_id']) ? NULL : $_POST['lecturer_id'];
    $super_id = empty($_POST['supervisor_id']) ? NULL : $_POST['supervisor_id'];
    $company  = mysqli_real_escape_string($conn, $_POST['company_name']);

    $pic_filename = $student_data['profile_picture'] ?? 'default.png';

    mysqli_begin_transaction($conn);
    try {
        if (isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1') {
            $pic_filename = 'default.png';
        } elseif (!empty($_POST['cropped_image_data'])) {
            $data = explode(',', $_POST['cropped_image_data']);
            $img_data = base64_decode($data[1]);
            $pic_filename = 'student_' . $student_id . '_' . time() . '.jpg';
            file_put_contents(__DIR__ . '/uploads/' . $pic_filename, $img_data);
        }

        // Update students table
        $stmt = mysqli_prepare($conn, "UPDATE students SET student_name=?, programme_id=?, lecturer_id=?, supervisor_id=?, profile_picture=? WHERE student_id=?");
        mysqli_stmt_bind_param($stmt, "ssssss", $sname, $pid, $lect_id, $super_id, $pic_filename, $student_id);
        mysqli_stmt_execute($stmt);

        // Update internships
        $stmt_i = mysqli_prepare($conn, "UPDATE internships SET company_name=? WHERE student_id=?");
        mysqli_stmt_bind_param($stmt_i, "ss", $company, $student_id);
        mysqli_stmt_execute($stmt_i);

        // Update users
        $stmt_u = mysqli_prepare($conn, "UPDATE users SET full_name=? WHERE user_id=?");
        mysqli_stmt_bind_param($stmt_u, "ss", $sname, $student_id);
        mysqli_stmt_execute($stmt_u);

        mysqli_commit($conn);
        // Pass the name as a URL parameter
header("Location: edit_student.php?id=$student_id&msg=updated&name=" . urlencode($sname));
exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $msg = "<div class='error-banner'>Error: " . $e->getMessage() . "</div>";
    }
}

// DROPDOWN DATA
$prog_result = mysqli_query($conn, "SELECT * FROM programmes");
$lect_result = mysqli_query($conn, "SELECT user_id, full_name FROM users WHERE role = 'Lecturer' ORDER BY full_name ASC");
$super_result = mysqli_query($conn, "SELECT user_id, full_name FROM users WHERE role = 'Supervisor' ORDER BY full_name ASC");
$company_result = mysqli_query($conn, "SELECT DISTINCT company_name FROM internships WHERE company_name != '' ORDER BY company_name ASC");

$db_pic = $student_data['profile_picture'] ?? '';
$has_db_pic = (!empty($db_pic) && $db_pic !== 'default.png');
$photo_src = $has_db_pic ? 'uploads/' . htmlspecialchars($db_pic) : '';
$name_initial = strtoupper(mb_substr(trim($student_data['student_name'] ?? 'S'), 0, 1));

include("header.php");
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">

<div class="edit-page-wrap">
    <div class="page-header-flex">
        <div>
            <span class="stat-label">Registry Action</span>
            <h1 class="page-title-main">Edit Student Profile</h1>
            <a href="view_students.php" class="back-link">&larr; Back to Registry</a>
        </div>
    </div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
    <div class="edit-notice success">
        <span class="notice-icon">✓</span>
        <div>Record for <strong><?php echo htmlspecialchars(urldecode($_GET['name'] ?? 'Student')); ?></strong> updated successfully.</div>
    </div>
<?php endif; ?>

    <div class="edit-form-card">
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <div class="form-section-header"><h2>Student Identity & Photo</h2></div>
            <div class="form-body">
                <div class="aa-form-group form-group-photo">
                    <label>Profile Photo</label>
                    <div class="photo-zone-container" id="photoZone">
                        <img id="photoPreview" class="preview-full <?php echo $has_db_pic ? '' : 'hidden-element'; ?>" src="<?php echo $photo_src; ?>"
                             onerror="this.classList.add('hidden-element'); document.getElementById('photoPlaceholder').classList.remove('hidden-element'); this.onerror=null;">
                        <div id="photoPlaceholder" class="photo-placeholder <?php echo $has_db_pic ? 'hidden-element' : ''; ?>">
                            <div class="letter-avatar"><?php echo htmlspecialchars($name_initial); ?></div>
                        </div>
                        <div class="photo-links-bar">
                            <span class="photo-upload-link" onclick="document.getElementById('photoInput').click()">Click to upload image</span>
                            <span id="btnRemovePhoto" class="photo-remove-link <?php echo $has_db_pic ? '' : 'hidden-element'; ?>" onclick="window.removeProfilePhoto()">Remove Photo</span>
                        </div>
                        <input type="file" id="photoInput" class="hidden-element" accept="image/*">
                        <input type="hidden" name="remove_photo" id="removePhotoFlag" value="0">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="fg"><label>Student ID</label><input type="text" value="<?php echo htmlspecialchars($student_data['student_id']); ?>" readonly class="field-input-muted"></div>
                    <div class="fg"><label>Full Name</label><input type="text" name="student_name" required value="<?php echo htmlspecialchars($student_data['student_name']); ?>"></div>
                    <div class="fg span-2">
                        <label>Academic Programme</label>
                        <select name="programme_id" class="search-select" required>
                            <?php while ($row = mysqli_fetch_assoc($prog_result)): ?>
                                <option value="<?php echo $row['programme_id']; ?>" <?php if ($student_data['programme_id'] == $row['programme_id']) echo 'selected'; ?>>
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
                            <option value="">-- Unassigned --</option>
                            <?php while ($row = mysqli_fetch_assoc($lect_result)): ?>
                                <option value="<?php echo $row['user_id']; ?>" <?php if ($student_data['lecturer_id'] == $row['user_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['full_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="fg">
                        <label>Assigned Supervisor</label>
                        <select name="supervisor_id" class="search-select">
                            <option value="">-- Unassigned --</option>
                            <?php while ($row = mysqli_fetch_assoc($super_result)): ?>
                                <option value="<?php echo $row['user_id']; ?>" <?php if ($student_data['supervisor_id'] == $row['user_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['full_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="fg span-2">
                        <label>Internship Company</label>
                        <select name="company_name" class="search-select">
                            <option value="">-- Pending Placement --</option>
                            <?php while ($row = mysqli_fetch_assoc($company_result)): ?>
                                <option value="<?php echo htmlspecialchars($row['company_name']); ?>" <?php if ($student_data['company_name'] == $row['company_name']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['company_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-footer">
                <div class="footer-actions">
                    <a href="view_students.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-save">Save Updates</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="cropModal" class="crop-modal">
    <div class="crop-modal-content">
        <h3>Adjust Photo</h3>
        <div class="crop-container"><img id="imageToCrop" src="" style="max-width:100%;"></div>
        <div class="crop-actions"><button type="button" class="btn-cancel" onclick="window.closeCropModal()">Cancel</button><button type="button" class="btn-save" id="btnCropConfirm">Confirm</button></div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="script.js?v=<?php echo time(); ?>"></script>
<?php include("footer.php"); ?>