<?php
/**
 * ============================================
 * USER LOGIN PAGE
 * ============================================
 */

require_once 'config/config.php';

// If already logged in, redirect to account
if (is_logged_in()) {
    header('Location: account.php');
    exit;
}

$error_msg = '';
$success_msg = $_GET['success'] ?? '';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = clean_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_msg = "Please enter both email and password.";
    } else {
        // Fetch user
        $user = db_fetch("SELECT user_id, email, password, first_name, is_active FROM users WHERE email = ? AND role = 'customer'", [$email]);
        
        if ($user) {
            if (!$user['is_active']) {
                $error_msg = "Your account is inactive. Please contact support.";
            } else if (password_verify($password, $user['password'])) {
                // Success - Set session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['first_name'];
                
                // Update last login
                db_query("UPDATE users SET last_login = NOW() WHERE user_id = ?", [$user['user_id']]);
                
                // Redirect - either to account or back to checkout if referring
                $redirect = $_SESSION['redirect_after_login'] ?? 'account.php';
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect");
                exit;
            } else {
                $error_msg = "Invalid email or password.";
            }
        } else {
            $error_msg = "Invalid email or password.";
        }
    }
}

// Include header
include 'includes/header.php';
?>

<section class="section" style="background: var(--bg-tertiary); min-height: 80vh; display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-center">
            <div class="col-6 col-md-5">
                <div style="background: white; padding: 2.5rem; border-radius: 20px; box-shadow: var(--shadow-lg);">
                    <div style="text-align: center; margin-bottom: 3rem;">
                        <i class="fas fa-lock" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1.5rem;"></i>
                        <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Welcome Back</h2>
                        <p style="color: var(--text-secondary);">Login to manage your orders & wishlist.</p>
                    </div>

                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger" style="margin-bottom: 2rem;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_msg === 'registered'): ?>
                        <div class="alert alert-success" style="margin-bottom: 2rem;">
                            <i class="fas fa-check-circle"></i> Registration successful! Please login to continue.
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="your@email.com" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <label class="form-label" style="margin: 0;">Password</label>
                                <a href="forgot-password.php" style="font-size: 0.815rem; color: var(--primary-color); text-decoration: none;">Forgot Password?</a>
                            </div>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('password', this)"></i>
                            </div>
                        </div>

                        <script>
                        function togglePassword(inputId, icon) {
                            const input = document.getElementById(inputId);
                            if (input.type === 'password') {
                                input.type = 'text';
                                icon.classList.remove('fa-eye');
                                icon.classList.add('fa-eye-slash');
                            } else {
                                input.type = 'password';
                                icon.classList.remove('fa-eye-slash');
                                icon.classList.add('fa-eye');
                            }
                        }
                        </script>

                        <div class="form-group" style="margin-bottom: 2rem;">
                            <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; font-weight: normal;">
                                <input type="checkbox" name="remember" style="width: auto;">
                                <span style="font-size: 0.875rem; color: var(--text-secondary);">Remember me</span>
                            </label>
                        </div>

                        <button type="submit" name="login" class="btn btn-primary btn-lg btn-block" style="padding: 1rem;">
                            Sign In
                        </button>
                    </form>

                    <div style="text-align: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                        <p style="color: var(--text-secondary);">New to <?php echo SITE_NAME; ?>?</p>
                        <a href="register.php" class="btn btn-outline btn-block">Create an Account</a>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="index.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.875rem;">
                        <i class="fas fa-arrow-left"></i> Back to Homepage
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
