<?php
session_start();
require("database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'User';
$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
        $message_type = "error";
    } elseif (strlen($new_password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $db_password = $row['password'];
            if (password_verify($current_password, $db_password)) {
                $final_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $update_stmt->bind_param("ss", $final_new_password, $user_id);
                if ($update_stmt->execute()) {
                    $message = "Password updated successfully.";
                    $message_type = "success";
                } else {
                    $message = "Database error. Could not update password.";
                    $message_type = "error";
                }
            } else {
                $message = "Incorrect current password.";
                $message_type = "error";
            }
        } else {
            $message = "User not found.";
            $message_type = "error";
        }
    }
}

$dash_link = match(ucfirst(strtolower(trim($role)))) {
    'Lecturer'   => 'lecturer_dashboard.php',
    'Supervisor' => 'supervisor_dashboard.php',
    'Admin'      => 'admin_dashboard.php',
    default      => 'index.php',
};

$normalized_role = ucfirst(strtolower(trim($role)));

if ($normalized_role === 'Lecturer') {
    $theme_grad_start = '#1e3a8a';
    $theme_grad_end   = '#2563eb';
    $theme_focus      = '#2563eb';
    $theme_focus_ring = 'rgba(37,99,235,0.08)';
    $theme_btn_start  = '#1e3a8a';
    $theme_btn_end    = '#2563eb';
    $theme_shadow     = 'rgba(37,99,235,0.25)';
} elseif ($normalized_role === 'Supervisor') {
    $theme_grad_start = '#064e3b';
    $theme_grad_end   = '#059669';
    $theme_focus      = '#059669';
    $theme_focus_ring = 'rgba(5,150,105,0.08)';
    $theme_btn_start  = '#064e3b';
    $theme_btn_end    = '#059669';
    $theme_shadow     = 'rgba(5,150,105,0.25)';
} else {
    $theme_grad_start = '#111827';
    $theme_grad_end   = '#374151';
    $theme_focus      = '#374151';
    $theme_focus_ring = 'rgba(55,65,81,0.08)';
    $theme_btn_start  = '#111827';
    $theme_btn_end    = '#374151';
    $theme_shadow     = 'rgba(17,24,39,0.25)';
}

include("header.php");
?>

<link rel="stylesheet" href="setting.css">
<style>
    /* Role-based theme variables — set by PHP */
    :root {
        --theme-grad-start: <?php echo $theme_grad_start; ?>;
        --theme-grad-end:   <?php echo $theme_grad_end; ?>;
        --theme-focus:      <?php echo $theme_focus; ?>;
        --theme-focus-ring: <?php echo $theme_focus_ring; ?>;
        --theme-btn-start:  <?php echo $theme_btn_start; ?>;
        --theme-btn-end:    <?php echo $theme_btn_end; ?>;
        --theme-shadow:     <?php echo $theme_shadow; ?>;
    }
</style>

<div class="settings-page">
    <div class="settings-wrapper">

        <!-- Back link -->
        <a href="<?php echo htmlspecialchars($dash_link); ?>" class="s-back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
            </svg>
            Back to Dashboard
        </a>

        <div class="s-card">

            <!-- Header -->
            <div class="s-card-head">
                <div class="s-head-top">
                    <div class="s-head-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <span class="s-role-chip"><?php echo htmlspecialchars(ucfirst(strtolower($role))); ?></span>
                </div>
                <h1>Account Security</h1>
                <p>Update your password to keep your account protected.</p>
            </div>

            <!-- Body -->
            <div class="s-card-body">

                <?php if ($message): ?>
                <div class="s-alert s-alert-<?php echo $message_type; ?>">
                    <?php if ($message_type === 'success'): ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    <?php else: ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="setting.php">
                    <input type="hidden" name="change_password" value="1">

                    <!-- Current Password -->
                    <div class="s-field">
                        <label class="s-label" for="current_password">Current Password</label>
                        <div class="s-input-wrap">
                            <span class="s-input-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input type="password" id="current_password" name="current_password" class="s-input" placeholder="Enter current password" required>
                            <button type="button" class="s-eye-btn" onclick="togglePassword('current_password', this)"></button>
                        </div>
                    </div>

                    <div class="s-divider"></div>

                    <!-- New Password -->
                    <div class="s-field">
                        <label class="s-label" for="new_password">New Password</label>
                        <div class="s-input-wrap">
                            <span class="s-input-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                            </span>
                            <input type="password" id="new_password" name="new_password" class="s-input" placeholder="Minimum 6 characters" required oninput="checkStrength(this.value)">
                            <button type="button" class="s-eye-btn" onclick="togglePassword('new_password', this)"></button>
                        </div>
                        <!-- Strength indicator -->
                        <div class="s-strength" id="strengthMeter">
                            <div class="s-strength-bars">
                                <div class="s-bar"></div>
                                <div class="s-bar"></div>
                                <div class="s-bar"></div>
                                <div class="s-bar"></div>
                            </div>
                            <span class="s-strength-text" id="strengthLabel">Too short</span>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="s-field">
                        <label class="s-label" for="confirm_password">Confirm New Password</label>
                        <div class="s-input-wrap">
                            <span class="s-input-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                            </span>
                            <input type="password" id="confirm_password" name="confirm_password" class="s-input" placeholder="Re-enter new password" required>
                            <button type="button" class="s-eye-btn" onclick="togglePassword('confirm_password', this)"></button>
                        </div>
                    </div>

                    <button type="submit" class="s-submit">
                        <span>Update Password</span>
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>

<script src="script.js"></script>

<?php include("footer.php"); ?>