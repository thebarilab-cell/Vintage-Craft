<?php
/**
 * ============================================
 * SHOPPING CART PAGE
 * ============================================
 */

require_once 'config/config.php';

// Page metadata
$page_title = 'Shopping Cart';
$meta_description = 'Review your cart and proceed to checkout.';

// Get user ID or session ID
if (is_logged_in()) {
    $user_id = get_user_id();
    $session_id = null;
} else {
    $user_id = null;
    $session_id = get_session_id();
}

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'update' && isset($_POST['cart_id']) && isset($_POST['quantity'])) {
            $cart_id = intval($_POST['cart_id']);
            $quantity = max(1, intval($_POST['quantity']));
            
            // Update quantity
            db_query("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE cart_id = ?", [$quantity, $cart_id]);
            header('Location: cart.php');
            exit;
        }
        
        if ($action === 'remove' && isset($_POST['cart_id'])) {
            $cart_id = intval($_POST['cart_id']);
            db_query("DELETE FROM cart WHERE cart_id = ?", [$cart_id]);
            header('Location: cart.php');
            exit;
        }
        
        if ($action === 'clear') {
            if ($user_id) {
                db_query("DELETE FROM cart WHERE user_id = ?", [$user_id]);
            } else {
                db_query("DELETE FROM cart WHERE session_id = ?", [$session_id]);
            }
            header('Location: cart.php');
            exit;
        }
    }
}

// Fetch cart items
if ($user_id) {
    $cart_items = db_fetch_all("
        SELECT c.*, p.name, p.price, p.discount_price, p.stock_quantity, p.slug,
               pi.image_path
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ", [$user_id]);
} else {
    $cart_items = db_fetch_all("
        SELECT c.*, p.name, p.price, p.discount_price, p.stock_quantity, p.slug,
               pi.image_path
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        WHERE c.session_id = ?
        ORDER BY c.created_at DESC
    ", [$session_id]);
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $price = $item['discount_price'] ?? $item['price'];
    $subtotal += $price * $item['quantity'];
}

$shipping = calculate_shipping($subtotal);
$total = $subtotal + $shipping;

// Calculate 50% advance payment
$advance_payment = calculate_advance_payment($total);
$remaining_payment = calculate_remaining_payment($total);

// Include header
include 'includes/header.php';
?>

<!-- ============================================
     PAGE HEADER
     ============================================ -->
<section style="background: var(--bg-tertiary); padding: 2rem 0; margin-bottom: 2rem;">
    <div class="container">
        <h1 style="margin-bottom: 0.5rem;">Shopping Cart</h1>
        <p style="color: var(--text-secondary);">
            <?php echo count($cart_items); ?> item<?php echo count($cart_items) !== 1 ? 's' : ''; ?> in your cart
        </p>
    </div>
</section>

<!-- ============================================
     CART CONTENT
     ============================================ -->
<section class="section-sm">
    <div class="container">
        <?php if (!empty($cart_items)): ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-8">
                    <div style="background: var(--bg-primary); border-radius: 12px; padding: 2rem; box-shadow: var(--shadow-sm);">
                        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Cart Items</h2>
                        
                        <?php foreach ($cart_items as $item): ?>
                            <?php
                            $price = $item['discount_price'] ?? $item['price'];
                            $item_total = $price * $item['quantity'];
                            $in_stock = $item['stock_quantity'] >= $item['quantity'];
                            ?>
                            
                            <div style="display: grid; grid-template-columns: 120px 1fr 140px; gap: 1.5rem; padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 1rem; background: #fff; <?php echo !$in_stock ? 'opacity: 0.6;' : ''; ?>">
                                <!-- Product Image -->
                                <div style="flex-shrink: 0;">
                                    <a href="<?php echo BASE_URL; ?>product.php?slug=<?php echo $item['slug']; ?>">
                                        <img 
                                            src="<?php echo BASE_URL . ($item['image_path'] ?? 'assets/images/placeholder-product.jpg'); ?>" 
                                            alt="<?php echo htmlspecialchars($item['name']); ?>"
                                            style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);"
                                            onerror="this.src='https://placehold.co/120x120/f5f1e8/8b6f47?text=Product'"
                                        >
                                    </a>
                                </div>
                                
                                <!-- Product Details -->
                                <div style="display: flex; flex-direction: column; justify-content: space-between;">
                                    <div>
                                        <h3 style="font-size: 1.125rem; margin-bottom: 0.25rem;">
                                            <a href="<?php echo BASE_URL; ?>product.php?slug=<?php echo $item['slug']; ?>" style="color: var(--text-primary);">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </a>
                                        </h3>
                                        
                                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                                            <span style="font-size: 1.25rem; font-weight: 700; color: var(--primary-color);">
                                                <?php echo format_price($price); ?>
                                            </span>
                                            <?php if ($item['discount_price']): ?>
                                                <span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.875rem;">
                                                    <?php echo format_price($item['price']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!$in_stock): ?>
                                        <div style="color: var(--danger); font-size: 0.75rem; margin-bottom: 0.5rem;">
                                            <i class="fas fa-exclamation-triangle"></i> Only <?php echo $item['stock_quantity']; ?> left
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Controls Row -->
                                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                                        <form method="POST" style="display: flex; align-items: center; gap: 0.5rem;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                            
                                            <div style="display: flex; align-items: center; border: 1px solid var(--border-color); border-radius: 6px; overflow: hidden; background: var(--bg-secondary);">
                                                <button type="button" style="width: 32px; height: 32px; border: none; background: none; cursor: pointer; color: var(--text-muted);" onclick="const inp = this.parentElement.querySelector('.qty-input'); inp.value = Math.max(1, parseInt(inp.value) - 1);"><i class="fas fa-minus" style="font-size: 0.75rem;"></i></button>
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" class="qty-input" style="width: 40px; text-align: center; border: none; background: none; font-weight: 600; font-size: 0.875rem; -moz-appearance: textfield;">
                                                <button type="button" style="width: 32px; height: 32px; border: none; background: none; cursor: pointer; color: var(--text-muted);" onclick="const inp = this.parentElement.querySelector('.qty-input'); inp.value = Math.min(<?php echo $item['stock_quantity']; ?>, parseInt(inp.value) + 1);"><i class="fas fa-plus" style="font-size: 0.75rem;"></i></button>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-sm btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; height: 34px;">
                                                <i class="fas fa-sync-alt"></i> Update
                                            </button>
                                        </form>
                                        
                                        <form method="POST">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; color: var(--danger); border-color: var(--danger); height: 34px;">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Item Total -->
                                <div style="text-align: right; display: flex; flex-direction: column; justify-content: flex-start; padding-top: 0.5rem;">
                                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">Item Total</p>
                                    <p style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);">
                                        <?php echo format_price($item_total); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Clear Cart -->
                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--border-color);">
                            <form method="POST" onsubmit="return confirm('Are you sure you want to clear your cart?');">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger);">
                                    <i class="fas fa-trash-alt"></i> Clear Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-4">
                    <div style="background: var(--bg-primary); border-radius: 12px; padding: 2rem; box-shadow: var(--shadow-md); position: sticky; top: 2rem;">
                        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Order Summary</h2>
                        
                        <!-- Subtotal -->
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                            <span style="color: var(--text-secondary);">Subtotal</span>
                            <span style="font-weight: 600;"><?php echo format_price($subtotal); ?></span>
                        </div>
                        
                        <!-- Shipping -->
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                            <span style="color: var(--text-secondary);">Shipping</span>
                            <span style="font-weight: 600;">
                                <?php if ($shipping === 0): ?>
                                    <span style="color: var(--success);">FREE</span>
                                <?php else: ?>
                                    <?php echo format_price($shipping); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <?php if ($subtotal < FREE_SHIPPING_THRESHOLD && FREE_SHIPPING_THRESHOLD > 0): ?>
                            <div style="background: var(--bg-tertiary); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem;">
                                <i class="fas fa-truck" style="color: var(--primary-color);"></i>
                                Add <strong><?php echo format_price(FREE_SHIPPING_THRESHOLD - $subtotal); ?></strong> more for FREE shipping!
                            </div>
                        <?php endif; ?>
                        
                        <!-- Total -->
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; padding: 1.5rem; background: var(--bg-tertiary); border-radius: 8px;">
                            <span style="font-size: 1.125rem; font-weight: 600;">Total</span>
                            <span style="font-size: 1.75rem; font-weight: 700; color: var(--primary-color);">
                                <?php echo format_price($total); ?>
                            </span>
                        </div>
                        
                        <!-- 50% Payment Breakdown -->
                        <?php if (ENABLE_ADVANCE_PAYMENT): ?>
                            <div style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); padding: 1.5rem; border-radius: 12px; color: white; margin-bottom: 1.5rem;">
                                <p style="font-size: 0.875rem; margin-bottom: 1rem; opacity: 0.9;">50% Advance Payment System</p>
                                
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                                    <span>Pay Now (50%)</span>
                                    <strong><?php echo format_price($advance_payment); ?></strong>
                                </div>
                                
                                <div style="display: flex; justify-content: space-between; opacity: 0.8;">
                                    <span>Pay on Delivery (50%)</span>
                                    <strong><?php echo format_price($remaining_payment); ?></strong>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Checkout Button -->
                        <a href="<?php echo BASE_URL; ?>checkout.php" class="btn btn-primary btn-lg btn-block" style="margin-bottom: 1rem;">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                        
                        <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-outline btn-block">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                        
                        <!-- Trust Badges -->
                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; font-size: 0.875rem; color: var(--text-secondary);">
                                <i class="fas fa-shield-alt" style="color: var(--success);"></i>
                                <span>Secure Checkout</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; font-size: 0.875rem; color: var(--text-secondary);">
                                <i class="fas fa-truck" style="color: var(--success);"></i>
                                <span>Fast Delivery</span>
                            </div>
                            <div style="display: flex; align-items: start; gap: 0.75rem; font-size: 0.875rem; color: var(--text-secondary);">
                                <i class="fas fa-info-circle" style="color: var(--warning); margin-top: 0.2rem;"></i>
                                <span>Once an order has been placed, the payment will be non-refundable.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Empty Cart -->
            <div class="text-center" style="padding: 4rem 0;">
                <i class="fas fa-shopping-cart" style="font-size: 5rem; color: var(--text-muted); margin-bottom: 1.5rem;"></i>
                <h2 style="font-size: 2rem; margin-bottom: 1rem;">Your Cart is Empty</h2>
                <p style="color: var(--text-secondary); font-size: 1.125rem; margin-bottom: 2rem;">
                    Looks like you haven't added any items to your cart yet
                </p>
                <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i> Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
