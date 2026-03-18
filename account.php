<?php
/**
 * ============================================
 * USER ACCOUNT / PROFILE
 * ============================================
 */

require_once 'config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$page_title = 'My Account';
include 'includes/header.php';

$user_id = get_user_id();
$user_name = $_SESSION['user_name'] ?? 'User';

// Fetch recent orders
$orders = db_fetch_all("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
", [$user_id]);
?>

<section class="section" style="padding: 4rem 0; background: var(--bg-secondary);">
    <div class="container">
        <div class="row">
            <!-- Account Sidebar -->
            <div class="col-3">
                <div style="background: white; border-radius: 12px; box-shadow: var(--shadow-sm); overflow: hidden;">
                    <div style="padding: 2rem; background: var(--bg-tertiary); text-align: center; border-bottom: 3px solid var(--primary-color);">
                        <div style="width: 80px; height: 80px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; margin: 0 auto 1rem;">
                            <?php echo substr($user_name, 0, 1); ?>
                        </div>
                        <h3 style="margin: 0;"><?php echo htmlspecialchars($user_name); ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem;">Customer since <?php echo date('Y'); ?></p>
                    </div>
                    <ul style="list-style: none; padding: 1rem 0;">
                        <li><a href="account.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--primary-color); font-weight: 600; background: var(--bg-secondary); border-left: 4px solid var(--primary-color);"><i class="fas fa-th-large" style="width: 20px;"></i> Dashboard</a></li>
                        <li><a href="account-orders.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--text-secondary); transition: all 0.3s;"><i class="fas fa-shopping-bag" style="width: 20px;"></i> Orders</a></li>
                        <li><a href="wishlist.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--text-secondary); transition: all 0.3s;"><i class="fas fa-heart" style="width: 20px;"></i> Wishlist</a></li>
                        <li><a href="account-profile.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--text-secondary); transition: all 0.3s;"><i class="fas fa-user-edit" style="width: 20px;"></i> Edit Profile</a></li>
                        <li><a href="logout.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--danger); transition: all 0.3s;"><i class="fas fa-sign-out-alt" style="width: 20px;"></i> Logout</a></li>
                    </ul>
                </div>
            </div>

            <!-- Account Content -->
            <div class="col-9">
                <div style="background: white; border-radius: 12px; padding: 2.5rem; box-shadow: var(--shadow-sm);">
                    <h2 style="font-size: 1.75rem; margin-bottom: 2rem;">Overview</h2>
                    
                    <div class="row" style="margin-bottom: 3rem;">
                        <div class="col-4">
                            <div style="background: var(--bg-tertiary); padding: 1.5rem; border-radius: 10px; text-align: center;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);"><?php echo count($orders); ?></div>
                                <div style="color: var(--text-muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; margin-top: 0.5rem;">Recent Orders</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div style="background: var(--bg-tertiary); padding: 1.5rem; border-radius: 10px; text-align: center;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">0</div>
                                <div style="color: var(--text-muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; margin-top: 0.5rem;">Pending Items</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div style="background: var(--bg-tertiary); padding: 1.5rem; border-radius: 10px; text-align: center;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">0</div>
                                <div style="color: var(--text-muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; margin-top: 0.5rem;">Vouchers</div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h3 style="font-size: 1.25rem; margin: 0;">Recent Orders</h3>
                        <a href="account-orders.php" style="color: var(--primary-color); font-size: 0.875rem;">View All <i class="fas fa-arrow-right"></i></a>
                    </div>

                    <?php if (!empty($orders)): ?>
                        <div style="border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
                                <thead style="background: var(--bg-tertiary);">
                                    <tr>
                                        <th style="padding: 1rem;">Order #</th>
                                        <th style="padding: 1rem;">Date</th>
                                        <th style="padding: 1rem;">Total</th>
                                        <th style="padding: 1rem;">Status</th>
                                        <th style="padding: 1rem; text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr style="border-top: 1px solid var(--border-color);">
                                            <td style="padding: 1rem;"><strong>#<?php echo $order['order_number']; ?></strong></td>
                                            <td style="padding: 1rem; color: var(--text-secondary);"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td style="padding: 1rem;"><strong><?php echo format_price($order['total_amount']); ?></strong></td>
                                            <td style="padding: 1rem;"><span class="badge" style="background: rgba(122, 155, 118, 0.15); color: var(--success); text-transform: uppercase; font-size: 0.7rem; font-weight: 700;"><?php echo $order['order_status']; ?></span></td>
                                            <td style="padding: 1rem; text-align: right;">
                                                <a href="order-view.php?id=<?php echo $order['order_id']; ?>" class="btn btn-outline btn-sm">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 3rem; background: var(--bg-tertiary); border-radius: 10px; color: var(--text-muted);">
                            <i class="fas fa-shopping-bag" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>You haven't placed any orders yet.</p>
                            <a href="shop.php" class="btn btn-primary btn-sm" style="margin-top: 1rem;">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
