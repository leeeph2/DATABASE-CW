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
    header("Location: view_students.php");
    exit();
}

// 3. Handle the Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sname   = mysqli_real_escape_string($conn, $_POST['student_name']);
    $pid     = $_POST['programme_id'];
    $lid     = $_POST['supervisor_id'];
    $company = mysqli_real_escape_string($conn, $_POST['company_name']);

    mysqli_begin_transaction($conn);

    try {
        // --- PHOTO UPDATE LOGIC ---
        $profile_pic_sql = "";
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
            $file_ext    = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_ext)) {
                $upload_dir = __DIR__ . '/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

                $new_file_name = $student_id . '_updated_' . time() . '.' . $file_ext;
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_dir . $new_file_name)) {
                    $profile_pic_sql = ", profile_picture = '$new_file_name'";
                }
            }
        }

        // Update tables
        mysqli_query($conn, "UPDATE students SET student_name='$sname', programme_id='$pid', supervisor_id='$lid' $profile_pic_sql WHERE student_id='$student_id'");
        mysqli_query($conn, "UPDATE internships SET company_name='$company' WHERE student_id='$student_id'");
        mysqli_query($conn, "UPDATE users SET full_name='$sname' WHERE user_id='$student_id'");

        mysqli_commit($conn);
        $encoded_name = urlencode($sname);
        header("Location: edit_student.php?id=$student_id&msg=updated&name=$encoded_name");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $msg = "error";
    }
}

// 4. Fetch current student data
$query = "SELECT s.*, i.company_name FROM students s LEFT JOIN internships i ON s.student_id = i.student_id WHERE s.student_id = '$student_id'";
$result = mysqli_query($conn, $query);
$student_data = mysqli_fetch_assoc($result);

if (!$student_data) {
    header("Location: view_students.php");
    exit();
}

$prog_result    = mysqli_query($conn, "SELECT * FROM programmes");
$lect_result    = mysqli_query($conn, "SELECT user_id, full_name FROM users WHERE role = 'Assessor'");
$company_result = mysqli_query($conn, "SELECT DISTINCT company_name FROM internships WHERE company_name != '' ORDER BY company_name ASC");

include("header.php");
?>

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
            <div>Record <strong><?php echo htmlspecialchars(urldecode($_GET['name'] ?? '')); ?></strong> successfully updated.</div>
        </div>
    <?php elseif ($msg === 'error'): ?>
        <div class="edit-notice error">
            <span class="notice-icon">✕</span>
            <div><strong>System Error:</strong> The update could not be completed.</div>
        </div>
    <?php endif; ?>

    <div class="edit-form-card">
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <div class="form-section-header"><h2>Student Identity & Photo</h2></div>

            <div class="form-body">
                <div class="aa-form-group" style="margin-bottom: 30px;">
    <label>Profile Photo</label>
    <div class="photo-zone" id="photoZone" style="max-width: 400px; margin: 0;">
        <?php 
        // Check if the student actually has a photo file in the uploads folder
        $has_pic = !empty($student_data['profile_picture']) && file_exists('uploads/' . $student_data['profile_picture']);
        $current_pic = $has_pic ? 'uploads/' . $student_data['profile_picture'] : '#';
        ?>
        
        <img id="photoPreview" class="preview" src="<?php echo $current_pic; ?>" 
             style="<?php echo $has_pic ? 'display:block;' : 'display:none;'; ?>">
        
        <div id="photoPlaceholder" style="<?php echo $has_pic ? 'display:none;' : 'display:block;'; ?>">
            <div class="photo-zone-title">Click or drag to upload</div>
            <div class="photo-zone-sub">JPG or PNG · Max 2 MB</div>
        </div>
        
        <input type="file" name="profile_picture" id="photoInput" accept="image/png,image/jpeg,image/webp" title="">
    </div>
    
</div>

                <div class="form-grid-2">
                    <div class="fg">
                        <label>Student ID</label>
                        <input type="text" value="<?php echo htmlspecialchars($student_data['student_id']); ?>" readonly style="background: #f9fafb;">
                        <span class="field-hint">Primary ID cannot be changed.</span>
                    </div>
                    <div class="fg">
                        <label>Full Name</label>
                        <input type="text" name="student_name" required value="<?php echo htmlspecialchars($student_data['student_name']); ?>">
                    </div>
                    <div class="fg span-2">
                        <label>Academic Programme</label>
                        <select name="programme_id" required>
                            <?php mysqli_data_seek($prog_result, 0); while($row = mysqli_fetch_assoc($prog_result)): ?>
                                <option value="<?php echo $row['programme_id']; ?>" <?php if($student_data['programme_id'] == $row['programme_id']) echo 'selected'; ?>>
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
                        <label>Assigned Assessor</label>
                        <select name="supervisor_id" required>
                            <?php mysqli_data_seek($lect_result, 0); while($row = mysqli_fetch_assoc($lect_result)): ?>
                                <option value="<?php echo $row['user_id']; ?>" <?php if($student_data['supervisor_id'] == $row['user_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['full_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="fg">
                        <label>Internship Company</label>
                        <select name="company_name" required>
                            <?php mysqli_data_seek($company_result, 0); while($row = mysqli_fetch_assoc($company_result)): ?>
                                <option value="<?php echo htmlspecialchars($row['company_name']); ?>" <?php if(($student_data['company_name'] ?? '') == $row['company_name']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row['company_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-footer" style="display: flex; justify-content: flex-end; padding: 20px 36px;">
                <div class="footer-actions" style="display: flex; gap: 12px;">
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
        <div class="crop-container"><img id="imageToCrop" src="" style="max-width: 100%;"></div>
        <div class="crop-actions">
            <button type="button" class="btn-cancel" onclick="closeCropModal()">Cancel</button>
            <button type="button" class="btn-save" id="btnCropConfirm">Confirm & Save</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="script.js?v=<?php echo time(); ?>"></script>
<?php include("footer.php"); ?>