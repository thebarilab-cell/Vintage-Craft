<?php
/**
 * ============================================
 * USER ORDER HISTORY
 * ============================================
 */

require_once 'config/config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$page_title = 'My Orders';
include 'includes/header.php';

$user_id = get_user_id();
$user_name = $_SESSION['user_name'] ?? 'User';

// Fetch all orders
$orders = db_fetch_all("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC
", [$user_id]);
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
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem;">Customer since <?php echo date('Y'); ?></p>
                    </div>
                    <ul style="list-style: none; padding: 1rem 0;">
                        <li><a href="account.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--text-secondary); transition: all 0.3s;"><i class="fas fa-th-large" style="width: 20px;"></i> Dashboard</a></li>
                        <li><a href="account-orders.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--primary-color); font-weight: 600; background: var(--bg-secondary); border-left: 4px solid var(--primary-color);"><i class="fas fa-shopping-bag" style="width: 20px;"></i> Orders</a></li>
                        <li><a href="wishlist.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--text-secondary); transition: all 0.3s;"><i class="fas fa-heart" style="width: 20px;"></i> Wishlist</a></li>
                        <li><a href="account-profile.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--text-secondary); transition: all 0.3s;"><i class="fas fa-user-edit" style="width: 20px;"></i> Edit Profile</a></li>
                        <li><a href="logout.php" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem 2rem; text-decoration: none; color: var(--danger); transition: all 0.3s;"><i class="fas fa-sign-out-alt" style="width: 20px;"></i> Logout</a></li>
                    </ul>
                </div>
            </div>

            <!-- Content -->
            <div class="col-9">
                <div style="background: white; border-radius: 12px; padding: 2.5rem; box-shadow: var(--shadow-sm);">
                    <h2 style="font-size: 1.75rem; margin-bottom: 2rem;">Order History</h2>
                    
                    <?php if (!empty($orders)): ?>
                        <div style="border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
                                <thead style="background: var(--bg-tertiary);">
                                    <tr>
                                        <th style="padding: 1.25rem;">Order Number</th>
                                        <th style="padding: 1.25rem;">Date</th>
                                        <th style="padding: 1.25rem;">Amount</th>
                                        <th style="padding: 1.25rem;">Order Status</th>
                                        <th style="padding: 1.25rem;">Payment</th>
                                        <th style="padding: 1.25rem; text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr style="border-top: 1px solid var(--border-color); transition: background 0.2s;" onmouseover="this.style.background='var(--bg-secondary)'" onmouseout="this.style.background='white'">
                                            <td style="padding: 1.25rem;"><strong>#<?php echo $order['order_number']; ?></strong></td>
                                            <td style="padding: 1.25rem; color: var(--text-secondary);"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td style="padding: 1.25rem;"><strong><?php echo format_price($order['total_amount']); ?></strong></td>
                                            <td style="padding: 1.25rem;">
                                                <span class="badge" style="padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; background: rgba(139, 111, 71, 0.1); color: var(--primary-color);">
                                                    <?php echo str_replace('_', ' ', $order['order_status']); ?>
                                                </span>
                                            </td>
                                            <td style="padding: 1.25rem;">
                                                <span class="status-indicator" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: var(--text-muted);">
                                                    <i class="fas fa-circle" style="font-size: 0.5rem; color: <?php echo $order['payment_status'] === 'paid' ? 'var(--success)' : 'var(--warning)'; ?>"></i>
                                                    <?php echo ucfirst($order['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td style="padding: 1.25rem; text-align: right;">
                                                <a href="order-view.php?id=<?php echo $order['order_id']; ?>" class="btn btn-outline btn-sm">Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 5rem 0; background: var(--bg-tertiary); border-radius: 10px; border: 2px dashed var(--border-color);">
                            <i class="fas fa-shopping-basket" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1.5rem; opacity: 0.3;"></i>
                            <h3 style="margin-bottom: 0.5rem;">No orders yet</h3>
                            <p style="color: var(--text-muted); margin-bottom: 2rem;">When you place an order, it will appear here.</p>
                            <a href="shop.php" class="btn btn-primary">Start Exploring</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
