<?php
/**
 * ============================================
 * ORDER DETAILS VIEW
 * ============================================
 */

require_once 'config/config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$order_id = intval($_GET['id'] ?? 0);
$user_id = get_user_id();

// Fetch order and ensure it belongs to the user
$order = db_fetch("SELECT * FROM orders WHERE order_id = ? AND user_id = ?", [$order_id, $user_id]);

if (!$order) {
    header('Location: account-orders.php');
    exit;
}

// Fetch order items
$items = db_fetch_all("
    SELECT oi.*, p.slug, pi.image_path 
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.product_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    WHERE oi.order_id = ?
", [$order_id]);

$page_title = 'Order #' . $order['order_number'];
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
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 3rem;">
                        <div>
                            <a href="account-orders.php" style="color: var(--text-muted); font-size: 0.875rem; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <i class="fas fa-arrow-left"></i> Back to Orders
                            </a>
                            <h2 style="font-size: 2rem; margin: 0;">Order #<?php echo $order['order_number']; ?></h2>
                            <p style="color: var(--text-muted); margin-top: 0.25rem;">Placed on <?php echo date('F d, Y \a\t h:i A', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div style="text-align: right;">
                            <span class="badge" style="padding: 0.6rem 1.25rem; border-radius: 30px; font-size: 0.875rem; font-weight: 700; text-transform: uppercase; background: var(--primary-color); color: white;">
                                <?php echo str_replace('_', ' ', $order['order_status']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="row" style="margin-bottom: 3rem;">
                        <div class="col-6">
                            <div style="padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 10px; height: 100%;">
                                <h4 style="margin-top: 0; margin-bottom: 1rem; color: var(--primary-color);"><i class="fas fa-map-marker-alt"></i> Shipping Address</h4>
                                <p style="margin: 0; font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($order['shipping_name']); ?></p>
                                <p style="margin: 0.25rem 0;"><?php echo htmlspecialchars($order['shipping_address_line1']); ?></p>
                                <p style="margin: 0;"><?php echo htmlspecialchars($order['shipping_city'] . ', ' . $order['shipping_state'] . ' ' . $order['shipping_postal_code']); ?></p>
                                <p style="margin: 0.75rem 0 0; color: var(--text-secondary);"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($order['shipping_phone']); ?></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 10px; height: 100%;">
                                <h4 style="margin-top: 0; margin-bottom: 1rem; color: var(--primary-color);"><i class="fas fa-credit-card"></i> Payment Summary</h4>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Method:</span>
                                    <strong><?php echo str_replace('_', ' ', strtoupper($order['payment_method'])); ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Status:</span>
                                    <strong style="color: <?php echo $order['payment_status'] === 'paid' ? 'var(--success)' : 'var(--warning)'; ?>"><?php echo strtoupper($order['payment_status']); ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding-top: 0.5rem; border-top: 1px dashed var(--border-color); margin-top: 0.5rem;">
                                    <span>Advance Paid:</span>
                                    <strong><?php echo format_price($order['advance_paid']); ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding-top: 0.25rem;">
                                    <span>Remaining:</span>
                                    <strong><?php echo format_price($order['remaining_payment_amount'] - $order['remaining_paid']); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">Order Items</h3>
                    <div style="margin-bottom: 3rem;">
                        <?php foreach ($items as $item): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 0; border-bottom: 1px solid var(--bg-tertiary);">
                                <div style="display: flex; gap: 1.5rem; align-items: center;">
                                    <div style="width: 70px; height: 70px; background: var(--bg-tertiary); border-radius: 8px; overflow: hidden; flex-shrink: 0;">
                                        <img src="<?php echo BASE_URL . ($item['image_path'] ?? 'assets/images/placeholder-product.jpg'); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div>
                                        <h4 style="margin: 0; font-size: 1rem;">
                                            <?php if ($item['slug']): ?>
                                                <a href="product.php?slug=<?php echo $item['slug']; ?>" style="color: var(--text-primary); text-decoration: none;"><?php echo htmlspecialchars($item['product_name']); ?></a>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                            <?php endif; ?>
                                        </h4>
                                        <p style="margin: 0.25rem 0 0; color: var(--text-muted); font-size: 0.8125rem;">SKU: <?php echo $item['product_sku'] ?: 'N/A'; ?></p>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <p style="margin: 0; font-weight: 600;"><?php echo format_price($item['unit_price']); ?> x <?php echo $item['quantity']; ?></p>
                                    <p style="margin: 0.25rem 0 0; font-size: 1.125rem; font-weight: 700; color: var(--primary-color);"><?php echo format_price($item['total_price']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="max-width: 350px; margin-left: auto;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                            <span style="color: var(--text-secondary);">Subtotal</span>
                            <span><?php echo format_price($order['subtotal']); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                            <span style="color: var(--text-secondary);">Shipping</span>
                            <span><?php echo $order['shipping_cost'] == 0 ? 'FREE' : format_price($order['shipping_cost']); ?></span>
                        </div>
                        <?php if ($order['discount_amount'] > 0): ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; color: var(--danger);">
                                <span>Discount</span>
                                <span>-<?php echo format_price($order['discount_amount']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div style="display: flex; justify-content: space-between; margin-top: 1rem; padding-top: 1rem; border-top: 2px solid var(--bg-tertiary);">
                            <strong style="font-size: 1.25rem;">Order Total</strong>
                            <strong style="font-size: 1.5rem; color: var(--primary-color);"><?php echo format_price($order['total_amount']); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
