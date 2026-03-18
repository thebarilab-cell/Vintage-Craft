<?php
/**
 * ============================================
 * RESET PASSWORD PAGE
 * ============================================
 */

require_once 'config/config.php';

// If already logged in, redirect to account
if (is_logged_in()) {
    header('Location: account.php');
    exit;
}

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

$error_msg = '';
$success_msg = '';
$is_valid_token = false;

if (empty($token) || empty($email)) {
    $error_msg = "Invalid or expired password reset link.";
} else {
    // Validate token
    $user = db_fetch("
        SELECT user_id 
        FROM users 
        WHERE email = ? AND reset_token = ? AND reset_token_expiry > NOW()
    ", [$email, $token]);
    
    if ($user) {
        $is_valid_token = true;
    } else {
        $error_msg = "This password reset link is invalid or has expired.";
    }
}

// Handle Reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password']) && $is_valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (strlen($password) < 8) {
        $error_msg = "Password must be at least 8 characters long.";
    } else if ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } else {
        // Hash and Update
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $updated = db_query("
            UPDATE users 
            SET password = ?, reset_token = NULL, reset_token_expiry = NULL 
            WHERE user_id = ?
        ", [$hashed_password, $user['user_id']]);
        
        if ($updated) {
            $success_msg = "Success! Your password has been reset. You can now login with your new password.";
            $is_valid_token = false; // Prevent resubmission
        } else {
            $error_msg = "Failed to update password. Please try again later.";
        }
    }
}

$page_title = 'Reset Password';
include 'includes/header.php';
?>

<section class="section" style="background: var(--bg-tertiary); min-height: 80vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-center">
            <div class="col-6 col-md-5">
                <div style="background: white; padding: 2.5rem; border-radius: 20px; box-shadow: var(--shadow-lg);">
                    <div style="text-align: center; margin-bottom: 3rem;">
                        <i class="fas fa-shield-alt" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1.5rem;"></i>
                        <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;">New Password</h2>
                        <p style="color: var(--text-secondary);">Create a strong new password for your account.</p>
                    </div>

                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger" style="margin-bottom: 2rem;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_msg): ?>
                        <div class="alert alert-success" style="margin-bottom: 2rem;">
                            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                        </div>
                        <div style="text-align: center; margin-top: 2rem;">
                            <a href="login.php" class="btn btn-primary btn-block">Go to Login</a>
                        </div>
                    <?php elseif ($is_valid_token): ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required>
                            </div>

                            <div class="form-group" style="margin-bottom: 2.5rem;">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password" required>
                            </div>

                            <button type="submit" name="reset_password" class="btn btn-primary btn-lg btn-block" style="padding: 1rem;">
                                Reset Password
                            </button>
                        </form>
                    <?php else: ?>
                        <div style="text-align: center; margin-top: 2rem;">
                            <a href="forgot-password.php" class="btn btn-primary btn-block">Request New Link</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
