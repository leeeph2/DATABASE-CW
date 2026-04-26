<?php
session_start();
require("database.php");

// Allow large POST bodies for Base64-encoded cropped images
@ini_set('post_max_size', '16M');
@ini_set('upload_max_filesize', '16M');

// Make ALL mysqli errors throw exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 1. SECURITY: Only Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

$message       = "";
$assessor_data = null;

// 2. FETCH EXISTING DATA
if (isset($_GET['id'])) {
    $target_id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$target_id'");
    if (mysqli_num_rows($query) > 0) {
        $assessor_data = mysqli_fetch_assoc($query);
    } else {
        die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>Assessor not found.</h2><a href='manage_assessors.php'>Go Back</a></div>");
    }
} else {
    header("Location: manage_assessors.php");
    exit();
}

// 3. UPDATE FUNCTIONALITY
if (isset($_POST['update_assessor'])) {
    $uid          = mysqli_real_escape_string($conn, $_POST['user_id']);
    $username     = mysqli_real_escape_string($conn, $_POST['username']);
    $full_name    = mysqli_real_escape_string($conn, $_POST['full_name']);
    
    // Password is now view-only, so we do not process it here.
    $pic_filename = $assessor_data['profile_picture'] ?? 'default.png';

    mysqli_begin_transaction($conn);

    try {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }

        $has_cropped_data = !empty($_POST['cropped_image_data'])
                            && strpos($_POST['cropped_image_data'], 'data:image/') === 0;

        if ($has_cropped_data) {
            $data_uri  = $_POST['cropped_image_data'];
            $comma_pos = strpos($data_uri, ',');
            $meta      = substr($data_uri, 0, $comma_pos);
            $b64       = substr($data_uri, $comma_pos + 1);
            $img_data  = base64_decode($b64);

            if ($img_data === false || strlen($img_data) === 0) {
                throw new Exception("Cropped image data was empty or corrupt.");
            }

            $ext = 'jpg';
            if (strpos($meta, 'image/png')  !== false) $ext = 'png';
            if (strpos($meta, 'image/webp') !== false) $ext = 'webp';

            // Delete old file
            $old_pic = $assessor_data['profile_picture'] ?? '';
            if (!empty($old_pic) && $old_pic !== 'default.png') {
                $old_path = $upload_dir . basename($old_pic);
                if (file_exists($old_path)) unlink($old_path);
            }

            $pic_filename = 'assessor_' . preg_replace('/[^a-z0-9]/i', '', $uid) . '_' . time() . '.' . $ext;
            file_put_contents($upload_dir . $pic_filename, $img_data);

        } elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            // Fallback for direct upload
            $file = $_FILES['profile_picture'];
            $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
            $pic_filename = 'assessor_' . preg_replace('/[^a-z0-9]/i', '', $uid) . '_' . time() . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $upload_dir . $pic_filename);
        }

        // Update database
        $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, full_name=?, profile_picture=? WHERE user_id=?");
        mysqli_stmt_bind_param($stmt, "ssss", $username, $full_name, $pic_filename, $uid);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);

        // Update local state for display
        $assessor_data['username']        = $username;
        $assessor_data['full_name']       = $full_name;
        $assessor_data['profile_picture'] = $pic_filename;

        $message = "<div class='edit-notice success' style='margin-bottom:20px;'>
                        <span class='notice-icon'>✓</span>
                        <div>Record for <strong>" . htmlspecialchars($full_name) . "</strong> updated.</div>
                    </div>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $message = "<div style='padding:15px; background:#fef2f2; color:#b91c1c; border-radius:8px; margin-bottom:20px;'>
                        <strong>ERROR:</strong> " . $e->getMessage() . "
                    </div>";
    }
}

$photo_src = 'uploads/' . htmlspecialchars($assessor_data['profile_picture'] ?? 'default.png');
include("header.php");
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">

<div class="edit-page-wrap">
    <div class="page-header-flex">
        <div>
            <span class="stat-label">Administration</span>
            <h1 class="page-title-main">Edit Staff Record</h1>
            <a href="manage_assessors.php" class="back-link">&larr; Back to Registry</a>
        </div>
    </div>

    <?php if ($message != "") echo $message; ?>

    <div class="edit-form-card">
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($assessor_data['user_id']); ?>">

            <div class="form-section-header"><h2>Staff Identity & Photo</h2></div>
            <div class="form-body">
                <div class="aa-form-group" style="margin-bottom:30px;">
                    <label>Profile Photo</label>
                    <div class="photo-zone" id="photoZone"
                         style="width:300px; height:250px; margin:0; display:flex; flex-direction:column;
                                align-items:center; justify-content:center; position:relative; overflow:hidden;">

                        <img id="photoPreview" class="preview" src="<?php echo $photo_src; ?>"
                             style="display:block;"
                             onerror="this.style.display='none'; document.getElementById('photoPlaceholder').style.display='flex';">

                        <div id="photoPlaceholder" style="display:none; flex-direction:column; align-items:center; gap:6px;">
                            <div class="photo-zone-title">No photo set</div>
                            <div class="photo-zone-sub">JPG, PNG or WEBP</div>
                        </div>

                        <div onclick="document.getElementById('photoInput').click()"
                             style="position:absolute; bottom:0; left:0; width:100%; text-align:center; padding:8px 0; 
                                    font-size:0.82rem; color:#6366f1; cursor:pointer; background:rgba(255,255,255,0.85); text-decoration:underline;">
                            Click to upload image
                        </div>
                        <input type="file" name="profile_picture" id="photoInput" accept="image/*" style="display:none;">
                    </div>
                    <div id="photoError" style="display:none; color:#b91c1c; font-size:0.85rem; margin-top:8px;"></div>
                </div>

                <div class="form-grid-2">
                    <div class="fg">
                        <label>Staff ID</label>
                        <input type="text" value="<?php echo htmlspecialchars($assessor_data['user_id']); ?>" 
                               readonly style="background:#f9fafb; cursor:not-allowed;">
                        <span class="field-hint">Primary ID is locked.</span>
                    </div>
                    <div class="fg">
                        <label>Password</label>
                        <input type="password" value="********" readonly style="background:#f9fafb; cursor:not-allowed;">
                        <span class="field-hint">Managed via security settings.</span>
                    </div>
                    <div class="fg">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($assessor_data['username']); ?>" required>
                    </div>
                    <div class="fg">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($assessor_data['full_name']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="form-footer" style="display:flex; justify-content:flex-end; padding:20px 36px;">
                <div class="footer-actions" style="display:flex; gap:12px;">
                    <a href="manage_assessors.php" class="btn-cancel">Cancel</a>
                    <button type="submit" name="update_assessor" class="btn-save">Save Updates</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="cropModal" class="crop-modal">
    <div class="crop-modal-content">
        <h3>Adjust Photo</h3>
        <div class="crop-container"><img id="imageToCrop" src="" style="max-width:100%;"></div>
        <div class="crop-actions">
            <button type="button" class="btn-cancel" onclick="closeCropModal()">Cancel</button>
            <button type="button" class="btn-save" id="btnCropConfirm">Confirm</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="script.js?v=<?php echo time(); ?>"></script>
<?php include("footer.php"); ?>