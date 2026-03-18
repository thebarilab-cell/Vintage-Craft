<?php
/**
 * ============================================
 * USER REGISTRATION PAGE
 * ============================================
 */

require_once 'config/config.php';

// If already logged in, redirect to account
if (is_logged_in()) {
    header('Location: account.php');
    exit;
}

$error_msg = '';

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $first_name = clean_input($_POST['first_name'] ?? '');
    $last_name = clean_input($_POST['last_name'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error_msg = "Please fill in all required fields.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } else if (strlen($password) < 8) {
        $error_msg = "Password must be at least 8 characters long.";
    } else if ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } else {
        // Check if email already exists
        $existing_user = db_fetch("SELECT user_id FROM users WHERE email = ?", [$email]);
        
        if ($existing_user) {
            $error_msg = "An account with this email already exists.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert user
            try {
                $inserted = db_query("
                    INSERT INTO users (first_name, last_name, email, phone, password, role, is_active, created_at) 
                    VALUES (?, ?, ?, ?, ?, 'customer', 1, NOW())
                ", [$first_name, $last_name, $email, $phone, $hashed_password]);
                
                if ($inserted) {
                    header('Location: login.php?success=registered');
                    exit;
                } else {
                    $error_msg = "An error occurred. Please try again later.";
                }
            } catch (Exception $e) {
                $error_msg = "System error: " . $e->getMessage();
            }
        }
    }
}

// Include header
include 'includes/header.php';
?>

<section class="section" style="background: var(--bg-tertiary); min-height: 90vh; display: flex; align-items: center; padding: 5rem 0;">
    <div class="container">
        <div class="row justify-center">
            <div class="col-10 col-md-8 col-lg-7">
                <div style="background: white; border-radius: 20px; box-shadow: var(--shadow-lg); display: flex; overflow: hidden;">
                    <!-- Left Side - Info (Optional aesthetic) -->
                    <div style="flex: 1; background: var(--primary-color); padding: 4rem; color: white; display: none; flex-direction: column; justify-content: center;">
                        <h2 style="color: white; font-size: 2.5rem; margin-bottom: 2rem;">Join the Community</h2>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: flex-start;">
                                <i class="fas fa-check-circle" style="margin-top: 5px;"></i>
                                <span>Save your favorite handcrafted items to your wishlist.</span>
                            </li>
                            <li style="margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: flex-start;">
                                <i class="fas fa-check-circle" style="margin-top: 5px;"></i>
                                <span>Track your orders and view purchase history.</span>
                            </li>
                            <li style="display: flex; gap: 1rem; align-items: flex-start;">
                                <i class="fas fa-check-circle" style="margin-top: 5px;"></i>
                                <span>Get exclusive updates on new artisan collections.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Right Side - Form -->
                    <div style="flex: 1.5; padding: 4rem;">
                        <div style="margin-bottom: 3rem;">
                            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Create Account</h2>
                            <p style="color: var(--text-secondary);">Join <?php echo SITE_NAME; ?> and start your artisan journey.</p>
                        </div>

                        <?php if ($error_msg): ?>
                            <div class="alert alert-danger" style="margin-bottom: 2rem;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">First Name *</label>
                                        <input type="text" name="first_name" class="form-control" placeholder="John" required value="<?php echo htmlspecialchars($first_name ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Last Name *</label>
                                        <input type="text" name="last_name" class="form-control" placeholder="Doe" required value="<?php echo htmlspecialchars($last_name ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" placeholder="john@example.com" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone Number (Optional)</label>
                                <input type="tel" name="phone" class="form-control" placeholder="+1 234 567 890" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Password *</label>
                                        <div class="password-wrapper">
                                            <input type="password" name="password" id="password" class="form-control" placeholder="Min. 8 characters" required>
                                            <i class="fas fa-eye password-toggle" onclick="togglePassword('password', this)"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="form-label">Confirm Password *</label>
                                        <div class="password-wrapper">
                                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repeat password" required>
                                            <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password', this)"></i>
                                        </div>
                                    </div>
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
                                <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; font-weight: normal;">
                                    <input type="checkbox" name="terms" style="width: auto; margin-top: 5px;" required checked>
                                    <span style="font-size: 0.815rem; color: var(--text-secondary); line-height: 1.4;">
                                        I agree to the <a href="terms.php" style="color: var(--primary-color);">Terms & Conditions</a> and <a href="privacy.php" style="color: var(--primary-color);">Privacy Policy</a>.
                                    </span>
                                </label>
                            </div>

                            <button type="submit" name="register" class="btn btn-primary btn-lg btn-block" style="padding: 1rem;">
                                Sign Up Now
                            </button>
                        </form>

                        <div style="text-align: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <p style="color: var(--text-secondary);">Already have an account?</p>
                            <a href="login.php" class="btn btn-outline btn-block">Sign In Instead</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
