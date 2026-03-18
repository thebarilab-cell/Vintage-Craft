<?php
/**
 * ============================================
 * USER PROFILE EDIT
 * ============================================
 */

require_once 'config/config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user_id = get_user_id();
$user = db_fetch("SELECT * FROM users WHERE user_id = ?", [$user_id]);

$page_title = 'Edit Profile';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = clean_input($_POST['first_name'] ?? '');
    $last_name = clean_input($_POST['last_name'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    
    if (empty($first_name) || empty($last_name)) {
        $error = "Name fields are required.";
    } else {
        $stmt = db_query("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE user_id = ?", [
            $first_name, $last_name, $phone, $user_id
        ]);
        
        if ($stmt) {
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $_SESSION['user_first_name'] = $first_name;
            $_SESSION['user_last_name'] = $last_name;
            $success = "Profile updated successfully!";
            // Update local user object
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['phone'] = $phone;
        } else {
            $error = "Failed to update profile.";
        }
    }
}

include 'includes/header.php';
$user_name = $_SESSION['user_name'] ?? 'User';
?>

<section class="section" style="padding: 4rem 0; background: var(--bg-secondary);">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-3">
                <div style="background: white; border-radius: 12px; box-shadow: var(--shadow-sm); overflow: hidden;">
                    <div style="padding: 2rem; background: var(--bg-tertiary); text-align: center; border-bottom: 3px solid var(--primary-color);">
                        <div style="width: 80px; height: 80px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; margin: 0 auto 1rem;">
                            <?php echo substr($user_name, 0, 1); ?>
                        </div>
                        <h3 style="margin: 0;"><?php echo htmlspecialchars($user_name); ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem;">Customer since <?php echo date('Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    <ul style="list-style: none; padding: 1rem 0;">
                        <li><a href="account.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--text-secondary); transition: all 0.3s;"><i class="fas fa-th-large" style="width: 20px;"></i> Dashboard</a></li>
                        <li><a href="account-orders.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--text-secondary); transition: all 0.3s;"><i class="fas fa-shopping-bag" style="width: 20px;"></i> Orders</a></li>
                        <li><a href="wishlist.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--text-secondary); transition: all 0.3s;"><i class="fas fa-heart" style="width: 20px;"></i> Wishlist</a></li>
                        <li><a href="account-profile.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--primary-color); font-weight: 600; background: var(--bg-secondary); border-left: 4px solid var(--primary-color);"><i class="fas fa-user-edit" style="width: 20px;"></i> Edit Profile</a></li>
                        <li><a href="logout.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--danger); transition: all 0.3s;"><i class="fas fa-sign-out-alt" style="width: 20px;"></i> Logout</a></li>
                    </ul>
                </div>
            </div>

            <!-- Content -->
            <div class="col-9">
                <div style="background: white; border-radius: 12px; padding: 2.5rem; box-shadow: var(--shadow-sm);">
                    <h2 style="font-size: 1.75rem; margin-bottom: 2rem;">Edit Profile</h2>

                    <?php if ($success): ?>
                        <div class="alert alert-success" style="margin-bottom: 2rem; padding: 1rem; background: rgba(122, 155, 118, 0.1); color: var(--success); border-radius: 8px; font-weight: 500;">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" style="margin-bottom: 2rem; padding: 1rem; background: rgba(198, 93, 59, 0.1); color: var(--danger); border-radius: 8px; font-weight: 500;">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" style="max-width: 600px;">
                        <div class="row" style="margin-bottom: 1.5rem;">
                            <div class="col-6">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.875rem;">First Name</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit;" required>
                            </div>
                            <div class="col-6">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.875rem;">Last Name</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit;" required>
                            </div>
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.875rem;">Email Address (Cannot be changed)</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; background: var(--bg-tertiary); cursor: not-allowed;" readonly title="Contact support to change email">
                        </div>

                        <div style="margin-bottom: 2rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.875rem;">Phone Number</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit;">
                        </div>

                        <button type="submit" class="btn btn-primary" style="padding: 1rem 2.5rem;">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
