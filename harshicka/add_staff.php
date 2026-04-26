<?php
session_start();
require("database.php");

// 1. SECURITY: Only Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php?error=unauthorized");
    exit();
}

// 2. REGISTER NEW STAFF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $user_id   = mysqli_real_escape_string($conn, trim($_POST['user_id'] ?? ''));
    $username  = mysqli_real_escape_string($conn, trim($_POST['username'] ?? ''));
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name'] ?? ''));
    $role      = mysqli_real_escape_string($conn, $_POST['role'] ?? '');
    $password  = $_POST['password'] ?? 'staff123'; 

    if (empty($user_id) || empty($username) || empty($full_name) || empty($password)) {
        header("Location: add_staff.php?error=missing_fields");
        exit();
    }

    if (!in_array($role, ['Lecturer', 'Supervisor'])) {
        header("Location: add_staff.php?error=invalid_role");
        exit();
    }

    $check = mysqli_query($conn, "SELECT user_id FROM users WHERE user_id = '$user_id' OR username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        header("Location: add_staff.php?error=duplicate");
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    
    // --- PROFILE PICTURE UPLOAD LOGIC ---
    $profile_pic = "default.png";

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_error = $_FILES['profile_picture']['error'];
        if ($upload_error !== UPLOAD_ERR_OK) {
            header("Location: add_staff.php?error=upload_failed");
            exit();
        }

        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        $file_ext    = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_ext)) {
            header("Location: add_staff.php?error=invalid_image_type");
            exit();
        }
        if ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
            header("Location: add_staff.php?error=upload_too_large");
            exit();
        }

        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $new_file_name = preg_replace('/[^A-Za-z0-9\-]/', '', $user_id) . '_' . time() . '.' . $file_ext;
        $dest_path     = $upload_dir . $new_file_name;
        
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dest_path)) {
            $profile_pic = $new_file_name;
        }
    }
    // -----------------------------------------

    // Insert into database, now including the profile_picture column!
    $sql = "INSERT INTO users (user_id, username, full_name, password, role, profile_picture)
            VALUES ('$user_id', '$username', '$full_name', '$hashed', '$role', '$profile_pic')";

    if (mysqli_query($conn, $sql)) {
        header("Location: manage_assessors.php?msg=registered&name=" . urlencode($full_name));
        exit();
    } else {
        header("Location: add_staff.php?error=create_failed");
        exit();
    }
}

include("header.php");
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">

<div class="page-header-flex">
    <div>
        <span class="stat-label">Faculty Management</span>
        <h1 class="page-title-main">Register New Staff</h1>
        <a href="manage_assessors.php" class="back-link">&larr; Back to Staff Records</a>
    </div>
</div>

<?php if (isset($_GET['error'])):
    $err_messages = [
        'duplicate'           => 'A staff with this ID or Username already exists.',
        'missing_fields'      => 'All fields are required.',
        'invalid_role'        => 'Invalid role selected.',
        'invalid_image_type'  => 'Invalid file. Only JPG and PNG are allowed.',
        'upload_too_large'    => 'Image exceeds the 2 MB limit.',
        'upload_failed'       => 'Failed to upload profile picture.',
        'create_failed'       => 'Database error while creating the record.',
    ];
    $emsg   = htmlspecialchars($err_messages[$_GET['error']] ?? 'An unexpected error occurred.');
?>
<div class="error-banner"><strong>Error:</strong> <?= $emsg ?></div>
<?php endif; ?>

<div class="wizard-shell">

    <aside class="wizard-sidebar">
        <div class="sidebar-card">
            <p class="sidebar-title">Registration Steps</p>

            <ul class="step-list" id="stepList">
                <li class="step-item active" data-step="1" onclick="goToStep(1)">
                    <div class="step-dot">1</div>
                    <div class="step-meta">
                        <div class="step-label">Role</div>
                        <div class="step-sublabel">Lecturer or Supervisor</div>
                    </div>
                </li>
                <li class="step-item" data-step="2" onclick="goToStep(2)">
                    <div class="step-dot">2</div>
                    <div class="step-meta">
                        <div class="step-label">Identity</div>
                        <div class="step-sublabel">ID, Name & Photo</div>
                    </div>
                </li>
                <li class="step-item" data-step="3" onclick="goToStep(3)">
                    <div class="step-dot">3</div>
                    <div class="step-meta">
                        <div class="step-label">Credentials</div>
                        <div class="step-sublabel">Username & Password</div>
                    </div>
                </li>
                <li class="step-item" data-step="4" onclick="goToStep(4)">
                    <div class="step-dot">4</div>
                    <div class="step-meta">
                        <div class="step-label">Review</div>
                        <div class="step-sublabel">Confirm & submit</div>
                    </div>
                </li>
            </ul>

            <div class="progress-wrap">
                <div class="progress-label">
                    <span>Progress</span>
                    <span id="progressPct">25%</span>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" id="progressBar" style="width:25%"></div>
                </div>
            </div>

        </div>
    </aside>

    <main class="wizard-main">
        <form method="POST" autocomplete="off" enctype="multipart/form-data" id="wizardForm" onsubmit="return handleSubmit()">

            <div class="form-panel active" id="panel1">
                <p class="panel-eyebrow">Step 1 of 4</p>
                <h2 class="panel-title">Select Staff Role</h2>
                <p class="panel-desc">Choose whether this staff member is a Lecturer or a Supervisor.</p>

                <div class="field-row" style="gap:20px; margin-bottom:28px;">
                    <label class="rs-role-card selected" id="card_lecturer">
                        <input type="radio" name="role" value="Lecturer" id="role_lecturer" checked onchange="window.syncRoleCards()">
                        <div class="rs-role-title">Lecturer</div>
                        <div class="rs-role-desc">Evaluates student internships. Directly assigned to student records.</div>
                        <div class="rs-role-check">✓</div>
                    </label>

                    <label class="rs-role-card" id="card_supervisor">
                        <input type="radio" name="role" value="Supervisor" id="role_supervisor" onchange="window.syncRoleCards()">
                        <div class="rs-role-title">Supervisor</div>
                        <div class="rs-role-desc">Industry supervisor who monitors students at the company.</div>
                        <div class="rs-role-check">✓</div>
                    </label>
                </div>

                <div class="panel-nav">
                    <span></span>
                    <button type="button" class="btn-next" onclick="nextStep(1)">
                        Next: Identity <span>→</span>
                    </button>
                </div>
            </div>

            <div class="form-panel" id="panel2">
                <p class="panel-eyebrow">Step 2 of 4</p>
                <h2 class="panel-title">Staff Identity</h2>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label>Profile Photo <span style="font-weight:400; color:var(--text-muted);">(optional)</span></label>
                    <div class="photo-zone" id="photoZone" style="width: 300px; height: 250px; margin: 0; display: flex; flex-direction: column; justify-content: center; position: relative;">
                        <img id="photoPreview" class="preview" src="#" alt="Preview" style="display:none; width: 100%; height: 100%; object-fit: cover;">
                        
                        <div id="photoPlaceholder">
                            <div class="photo-zone-title">Click or drag to upload</div>
                            <div class="photo-zone-sub">JPG or PNG · Max 2 MB</div>
                        </div>
                        
                        <input type="file" name="profile_picture" id="photoInput" accept="image/png,image/jpeg,image/webp" title="" style="position: absolute; inset: 0; opacity: 0; cursor: pointer;">
                    </div>
                    <p id="photoError" style="color:#dc2626; font-size:0.75rem; margin-top:6px; display:none;"></p>
                </div>

                <div class="field-row">
                    <div class="form-group">
                        <label>Staff ID <span class="required-star">*</span></label>
                        <input type="text" name="user_id" id="f_uid" required placeholder="e.g. STF001">
                    </div>

                    <div class="form-group">
                        <label>Full Name <span class="required-star">*</span></label>
                        <input type="text" name="full_name" id="f_fname" required placeholder="e.g. Dr. John Doe">
                    </div>
                </div> 
                
                <div class="panel-nav">
                    <button type="button" class="btn-back" onclick="prevStep(2)">← Back</button>
                    <button type="button" class="btn-next" onclick="nextStep(2)">
                        Next: Credentials <span>→</span>
                    </button>
                </div>
            </div>

            <div class="form-panel" id="panel3">
                <p class="panel-eyebrow">Step 3 of 4</p>
                <h2 class="panel-title">Account Credentials</h2>
                <p class="panel-desc">Set up the login details for this staff member.</p>

                <div class="form-group">
                    <label>Login Username <span class="required-star">*</span></label>
                    <input type="text" name="username" id="f_uname" required placeholder="e.g. johndoe">
                </div>

                <div class="form-group">
                    <label>Default Password</label>
                    <input type="text" name="password" value="staff123" readonly style="background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; padding: 10px; border-radius: 6px; width: 100%;">
                    <p class="field-hint">The system assigns a default password. They can change it upon first login.</p>
                </div>

                <div class="panel-nav">
                    <button type="button" class="btn-back" onclick="prevStep(3)">← Back</button>
                    <button type="button" class="btn-next" onclick="nextStep(3)">
                        Review Record <span>→</span>
                    </button>
                </div>
            </div>

            <div class="form-panel" id="panel4">
                <p class="panel-eyebrow">Step 4 of 4</p>
                <h2 class="panel-title">Review & Confirm</h2>
                <p class="panel-desc">Double-check all details before creating the staff account.</p>

                <div class="review-photo-row">
                    <img id="reviewPhoto" class="review-avatar" src="uploads/default.png" style="display:none;">
                    <img id="reviewAvatarPlaceholder" class="review-avatar" src="uploads/default.png" alt="Default Profile">
                    <div>
                        <div class="review-name" id="rev_fname">—</div>
                        <div class="review-sub" id="rev_uid">—</div>
                    </div>
                </div>

                <div class="review-grid">
                    <div class="review-item">
                        <div class="review-label">Staff ID</div>
                        <div class="review-val" id="rv_uid">—</div>
                    </div>
                    <div class="review-item">
                        <div class="review-label">Full Name</div>
                        <div class="review-val" id="rv_fname">—</div>
                    </div>
                    <div class="review-item">
                        <div class="review-label">Login Username</div>
                        <div class="review-val" id="rv_uname">—</div>
                    </div>
                    <div class="review-item">
                        <div class="review-label">Role</div>
                        <div class="review-val" id="rv_role">—</div>
                    </div>
                    <div class="review-item">
                        <div class="review-label">Password</div>
                        <div class="review-val">staff123 (Default)</div>
                    </div>
                </div>

                <div class="panel-nav">
                    <button type="button" class="btn-back" onclick="prevStep(4)">← Edit Details</button>
                    <button type="submit" class="btn-next" id="submitBtn">
                        ✓ Create Staff Account
                    </button>
                </div>
            </div>

        </form>
    </main>
</div>

<div id="cropModal" class="crop-modal">
    <div class="crop-modal-content">
        <h3>Adjust Profile Photo</h3>
        <div class="crop-container"><img id="imageToCrop" src="" style="max-width: 100%;"></div>
        <div class="crop-actions">
            <button type="button" class="btn-cancel" onclick="window.closeCropModal()">Cancel</button>
            <button type="button" class="btn-save" id="btnCropConfirm">Confirm & Save</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="script.js?v=<?php echo time(); ?>"></script>

<?php include("footer.php"); ?>