<?php
/**
 * ============================================
 * FORGOT PASSWORD PAGE
 * ============================================
 */

require_once 'config/config.php';

// If already logged in, redirect to account
if (is_logged_in()) {
    header('Location: account.php');
    exit;
}

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password'])) {
    $email = clean_input($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error_msg = "Please enter your email address.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } else {
        // Check if user exists
        $user = db_fetch("SELECT user_id, first_name FROM users WHERE email = ? AND role = 'customer'", [$email]);
        
        if ($user) {
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Update user with token
            db_query("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE user_id = ?", [
                $token, $expiry, $user['user_id']
            ]);
            
            // Simulate sending email
            // In a real scenario, use mail() or a library like PHPMailer
            $reset_link = BASE_URL . "reset-password.php?token=" . $token . "&email=" . urlencode($email);
            
            // For this demo/development, we'll show a "Success" message
            // and log the link for the developer (you) if needed.
            $success_msg = "A password reset link has been sent to your email address. Please check your inbox (and spam folder).";
            
            // DEBUG: Since we aren't sending real emails, let's make it easy to test
            // Note: In production, NEVER show the link on screen.
            if (IS_LOCALHOST) {
                $_SESSION['last_reset_link'] = $reset_link;
            }
        } else {
            // To prevent email enumeration, we show the same success message
            // even if the email doesn't exist.
            $success_msg = "If an account exists with that email, a password reset link has been sent.";
        }
    }
}

$page_title = 'Forgot Password';
include 'includes/header.php';
?>

<section class="section" style="background: var(--bg-tertiary); min-height: 80vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-center">
            <div class="col-6 col-md-5">
                <div style="background: white; padding: 2.5rem; border-radius: 20px; box-shadow: var(--shadow-lg);">
                    <div style="text-align: center; margin-bottom: 3rem;">
                        <i class="fas fa-key" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1.5rem;"></i>
                        <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Reset Password</h2>
                        <p style="color: var(--text-secondary);">Enter your email to receive a password reset link.</p>
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
                        
                        <?php if (IS_LOCALHOST && isset($_SESSION['last_reset_link'])): ?>
                            <div style="margin-top: 1rem; padding: 1rem; background: #fff8e1; border: 1px solid #ffe082; border-radius: 8px; font-size: 0.8125rem;">
                                <p style="margin: 0 0 0.5rem 0; font-weight: 700; color: #856404;">Dev Mode: Testing Link</p>
                                <a href="<?php echo $_SESSION['last_reset_link']; ?>" style="word-break: break-all; color: #856404;"><?php echo $_SESSION['last_reset_link']; ?></a>
                                <?php unset($_SESSION['last_reset_link']); ?>
                            </div>
                        <?php endif; ?>

                        <div style="text-align: center; margin-top: 2rem;">
                            <a href="login.php" class="btn btn-outline btn-block">Back to Login</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="form-group" style="margin-bottom: 2rem;">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="your@email.com" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                            </div>

                            <button type="submit" name="forgot_password" class="btn btn-primary btn-lg btn-block" style="padding: 1rem;">
                                Send Reset Link
                            </button>
                        </form>

                        <div style="text-align: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <p style="color: var(--text-secondary);">Remembered your password?</p>
                            <a href="login.php" class="btn btn-outline btn-block">Back to Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
