<?php
/**
 * Employee Portal — Change Password
 */
$page_title = 'Change Password';
require_once '../includes/session-check.php';
checkRole(['Employee']);
require_once '../includes/functions.php';

$user_id = (int)$_SESSION['user_id'];
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current  = $_POST['current_password']  ?? '';
    $new      = $_POST['new_password']      ?? '';
    $confirm  = $_POST['confirm_password']  ?? '';

    if (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        // Fetch current hash
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!password_verify($current, $row['password_hash'])) {
            $error = 'Current password is incorrect.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $upd  = $conn->prepare("UPDATE users SET password_hash=? WHERE user_id=?");
            $upd->bind_param("si", $hash, $user_id);
            if ($upd->execute()) {
                logAudit($conn, $user_id, 'CHANGE_PASSWORD', 'User', $user_id, 'Employee changed their password.');
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to update password. Please try again.';
            }
            $upd->close();
        }
    }
}

require_once '../includes/header.php';
?>

<div class="page-hero fadeup">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-0 gap-3">
        <div>
            <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.55);">Employee Portal · Security</div>
            <h4 class="text-white fw-bold mb-0 mt-1"><i class="fas fa-key me-2" style="color:var(--primary-light);"></i>Change Password</h4>
            <p class="text-white-50 small mb-0 mt-2 d-none d-sm-block">Update your security credentials to keep your account safe</p>
        </div>
        <div class="d-none d-md-block">
            <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<div class="d-md-none mb-4 fadeup" style="animation-delay: 0.1s;">
    <a href="<?php echo BASE_URL; ?>/employee/dashboard.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i>Back to My Dashboard
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="content-card fadeup-1">
            <div class="card-header">
                <h5><i class="fas fa-shield-alt me-2"></i>Security Update</h5>
            </div>
            <div class="card-body p-4">
                <?php if ($success): ?>
                    <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i><?php echo e($success); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                    </div>
                    <hr class="my-4 opacity-50">
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required minlength="6">
                        <div class="form-text small text-muted">Use a strong password with mixed characters.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="fas fa-save me-2"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
