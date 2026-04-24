<?php
// ============================================================
// STEP 1: Session, DB, Auth
// ============================================================
session_start();
require("database.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

// ============================================================
// STEP 2: Handle POST
// ============================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $uid  = strtoupper(trim(mysqli_real_escape_string($conn, $_POST['user_id']   ?? '')));
    $user = trim(mysqli_real_escape_string($conn, $_POST['username']  ?? ''));
    $name = trim(mysqli_real_escape_string($conn, $_POST['full_name'] ?? ''));
    $pass = $_POST['password'] ?? '';

    if (!$uid || !$user || !$name || !$pass) {
        header("Location: add_assessor.php?error=missing_fields");
        exit();
    }

    $hashed = password_hash($pass, PASSWORD_DEFAULT);

    // --- PHOTO UPLOAD LOGIC ---
    $profile_pic = "default.png";

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_error = $_FILES['profile_picture']['error'];
        if ($upload_error === UPLOAD_ERR_OK) {
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
            $file_ext    = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_ext)) {
                $upload_dir = __DIR__ . '/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

                $new_file_name = $uid . '_assessor_' . time() . '.' . $file_ext;
                $dest_path     = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dest_path)) {
                    $profile_pic = $new_file_name;
                }
            }
        }
    }

    // Check for duplicates
    $check = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$uid' OR username = '$user'");
    if (mysqli_num_rows($check) > 0) {
        header("Location: add_assessor.php?error=duplicate");
        exit();
    }

    // Insert into DB
    $query = "INSERT INTO users (user_id, username, password, full_name, role, profile_picture) 
              VALUES ('$uid', '$user', '$hashed', '$name', 'Assessor', '$profile_pic')";

    if (mysqli_query($conn, $query)) {
        header("Location: view_assessors.php?msg=registered&name=" . urlencode($name));
        exit();
    } else {
        header("Location: add_assessor.php?error=create_failed");
        exit();
    }
}

include("header.php");
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">

<div class="page-header-flex">
    <div>
        <span class="stat-label">Faculty Management</span>
        <h1 class="page-title-main">Register New Assessor</h1>
        <a href="view_assessors.php" class="back-link">&larr; Back to Assessor Records</a>
    </div>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="aa-error-box" style="display: flex; align-items: flex-start; gap: 12px; background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; font-size: 0.85rem;">
        <span style="font-size: 1.1rem; flex-shrink: 0;">⚠</span>
        <div><strong>Registration Failed —</strong> Check for duplicate IDs or missing fields.</div>
    </div>
<?php endif; ?>

<div class="aa-shell">

    <div class="aa-sidebar">
        <div class="aa-sidebar-card">
            <div class="aa-sidebar-eyebrow">Assessor Profile</div>
            <div class="aa-sidebar-title">New Staff Account</div>
        
            <div class="progress-wrap" style="margin-top: 24px;">
                <div class="progress-label">
                    <span>Form Completion</span>
                    <span id="progressPct">0%</span>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" id="progressBar" style="width:0%"></div>
                </div>
            </div>

            <div class="aa-role-badge" style="margin-top: 24px;">⬡ &nbsp;Role: Assessor</div>
        </div>
    </div>

    <div class="aa-form-card">
        <div class="aa-form-top">
            <div class="aa-form-eyebrow">Account Registration</div>
            <div class="aa-form-title">Staff Details</div>
            <div class="aa-form-desc">Please verify all identity and security details before submitting.</div>
        </div>

        <form id="wizardForm" method="POST" action="add_assessor.php" autocomplete="off" enctype="multipart/form-data" onsubmit="return validateAndSubmit()">
            <div class="aa-form-body">

                <div class="aa-section-label">Profile Photo (Optional)</div>
                <div class="aa-form-group">
                    <div class="photo-zone" id="photoZone" style="max-width: 400px; margin: 0;">
                        <img id="photoPreview" class="preview" src="#" alt="Preview">
                        <div id="photoPlaceholder">
                            <div class="photo-zone-title">Click or drag to upload</div>
                            <div class="photo-zone-sub">JPG or PNG · Square crop suggested</div>
                        </div>
                        <input type="file" name="profile_picture" id="photoInput" accept="image/png,image/jpeg,image/webp" title="">
                    </div>
                    <p id="photoError" style="color:#dc2626; font-size:0.75rem; margin-top:6px; display:none;"></p>
                </div>

                <div class="aa-section-label">Identity &amp; Access</div>
                <div class="aa-field-row">
                    <div class="aa-form-group">
                        <label>Staff ID <span class="required-star">*</span></label>
                        <input type="text" name="user_id" id="f_sid" required placeholder="e.g. LEC-005" style="text-transform:uppercase;">
                    </div>
                    <div class="aa-form-group">
                        <label>Username <span class="required-star">*</span></label>
                        <input type="text" name="username" id="f_prog" required placeholder="e.g. janesmith">
                    </div>
                </div>

                <div class="aa-section-label">Personal Details</div>
                <div class="aa-form-group">
                    <label>Full Name <span class="required-star">*</span></label>
                    <input type="text" name="full_name" id="f_sname" required placeholder="e.g. Dr. Jane Smith">
                </div>

                <div class="aa-section-label">Security</div>
                <div class="aa-form-group">
                    <label>Password <span class="required-star">*</span></label>
                    <div class="aa-password-wrap">
                        <input type="password" name="password" id="f_lect" required placeholder="Enter a secure password">
                    </div>
                </div>

            </div>

            <div class="aa-form-footer" style="display: flex; justify-content: flex-end;">
                <button type="submit" id="submitBtn" class="aa-submit-btn">
                    ＋ &nbsp;Register Assessor
                </button>
            </div>
        </form>
    </div>
</div>

<div id="cropModal" class="crop-modal">
    <div class="crop-modal-content">
        <h3 style="margin-top:0; margin-bottom:15px; color:var(--primary-dark);">Adjust Profile Photo</h3>
        <div class="crop-container">
            <img id="imageToCrop" src="" style="max-width: 100%; display: block;">
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