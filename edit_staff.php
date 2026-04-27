<?php
session_start();
require("database.php");

@ini_set('post_max_size', '16M');
@ini_set('upload_max_filesize', '16M');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

$message       = "";
$assessor_data = null;

if (isset($_GET['id'])) {
    $target_id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$target_id'");
    if (mysqli_num_rows($query) > 0) {
        $assessor_data = mysqli_fetch_assoc($query);
    } else {
        die("<div class='error-banner'><h2>Assessor not found.</h2><a href='manage_assessors.php'>Go Back</a></div>");
    }
} else {
    header("Location: manage_assessors.php");
    exit();
}

if (isset($_POST['update_assessor'])) {
    $uid       = mysqli_real_escape_string($conn, $_POST['user_id']);
    $username  = mysqli_real_escape_string($conn, $_POST['username']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    
    $pic_filename = $assessor_data['profile_picture'] ?? 'default.png';

    mysqli_begin_transaction($conn);

    try {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

        if (isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1') {
            $pic_filename = 'default.png';
            $old_pic = $assessor_data['profile_picture'] ?? '';
            if (!empty($old_pic) && $old_pic !== 'default.png') {
                $old_path = $upload_dir . basename($old_pic);
                if (file_exists($old_path)) @unlink($old_path);
            }
        } else {
            $has_cropped_data = !empty($_POST['cropped_image_data']) && strpos($_POST['cropped_image_data'], 'data:image/') === 0;

            if ($has_cropped_data) {
                $data_uri  = $_POST['cropped_image_data'];
                $comma_pos = strpos($data_uri, ',');
                $meta      = substr($data_uri, 0, $comma_pos);
                $b64       = substr($data_uri, $comma_pos + 1);
                $img_data  = base64_decode($b64);

                if ($img_data === false || strlen($img_data) === 0) throw new Exception("Cropped image data corrupt.");

                $ext = 'jpg';
                if (strpos($meta, 'image/png') !== false) $ext = 'png';
                if (strpos($meta, 'image/webp') !== false) $ext = 'webp';

                $old_pic = $assessor_data['profile_picture'] ?? '';
                if (!empty($old_pic) && $old_pic !== 'default.png') {
                    $old_path = $upload_dir . basename($old_pic);
                    if (file_exists($old_path)) @unlink($old_path);
                }

                $pic_filename = 'assessor_' . preg_replace('/[^a-z0-9]/i', '', $uid) . '_' . time() . '.' . $ext;
                file_put_contents($upload_dir . $pic_filename, $img_data);
            }
        }

        $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, full_name=?, profile_picture=? WHERE user_id=?");
        mysqli_stmt_bind_param($stmt, "ssss", $username, $full_name, $pic_filename, $uid);
        mysqli_stmt_execute($stmt);

        mysqli_commit($conn);
        // Redirect to include the name for the notification
        header("Location: edit_staff.php?id=$uid&msg=updated&name=" . urlencode($full_name));
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $message = "<div class='edit-notice error'><strong>ERROR:</strong> " . $e->getMessage() . "</div>";
    }
}

$db_pic      = $assessor_data['profile_picture'] ?? '';
$is_default  = empty($db_pic) || $db_pic === 'default.png';
$has_db_pic  = !$is_default;
$photo_src   = $has_db_pic ? 'uploads/' . htmlspecialchars($db_pic) : '';

$_raw_name    = trim($assessor_data['full_name'] ?? 'S');
$name_initial = strtoupper(mb_substr($_raw_name, 0, 1));

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

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
        <div class='edit-notice success'>
            <span class='notice-icon'>✓</span>
            <div>Record for <strong><?php echo htmlspecialchars(urldecode($_GET['name'] ?? 'Staff Member')); ?></strong> updated successfully.</div>
        </div>
    <?php endif; ?>

    <div class="edit-form-card">
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($assessor_data['user_id']); ?>">

            <div class="form-section-header"><h2>Staff Identity & Photo</h2></div>
            <div class="form-body">
                
                <div class="aa-form-group form-group-photo">
                    <label>Profile Photo</label>
                    <div class="photo-zone-container" id="photoZone">
                        <img id="photoPreview" class="preview-full <?php echo $has_db_pic ? '' : 'hidden-element'; ?>" src="<?php echo $photo_src; ?>"
                             onerror="this.classList.add('hidden-element'); document.getElementById('photoPlaceholder').classList.remove('hidden-element'); document.getElementById('btnRemovePhoto').classList.add('hidden-element'); this.onerror=null;">

                        <div id="photoPlaceholder" class="photo-placeholder <?php echo $has_db_pic ? 'hidden-element' : ''; ?>">
                            <div id="letterAvatar" class="letter-avatar"><?php echo htmlspecialchars($name_initial); ?></div>
                            <div class="photo-zone-sub">JPG, PNG or WEBP</div>
                        </div>

                        <div class="photo-links-bar">
                            <span class="photo-upload-link" onclick="document.getElementById('photoInput').click()">Click to upload image</span>
                            <span id="btnRemovePhoto" class="photo-remove-link <?php echo $has_db_pic ? '' : 'hidden-element'; ?>" onclick="window.removeProfilePhoto()">Remove Photo</span>
                        </div>
                        
                        <input type="file" id="photoInput" class="hidden-element" accept="image/*">
                        <input type="hidden" name="remove_photo" id="removePhotoFlag" value="0">
                    </div>
                </div>

                <h3 style="font-size: 0.8rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin: 30px 0 15px 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px;">
                    System Records (View Only)
                </h3>
                <div class="form-grid-2">
                    <div class="fg">
                        <label>Staff ID</label>
                        <input type="text" value="<?php echo htmlspecialchars($assessor_data['user_id']); ?>" readonly class="field-input-muted">
                    </div>
                    <div class="fg">
                        <label>Password</label>
                        <input type="password" value="********" readonly class="field-input-muted">
                        
                    </div>
                </div>

                <h3 style="font-size: 0.8rem; font-weight: 700; color:  #6b7280;; text-transform: uppercase; letter-spacing: 0.05em; margin: 25px 0 15px 0; border-bottom: 1px solid #e0e7ff; padding-bottom: 8px;">
                    Editable Information
                </h3>
                <div class="form-grid-2">
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

            <div class="form-footer">
                <div class="footer-actions">
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
            <button type="button" class="btn-cancel" onclick="window.closeCropModal()">Cancel</button>
            <button type="button" class="btn-save" id="btnCropConfirm">Confirm</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="script.js?v=<?php echo time(); ?>"></script>
<?php include("footer.php"); ?>