<?php
/**
 * ============================================
 * CHECKOUT PAGE
 * ============================================
 */

require_once 'config/config.php';

// Check if cart is empty
if (is_logged_in()) {
    $user_id = get_user_id();
    $cart_count_stmt = db_query("SELECT COUNT(*) as count FROM cart WHERE user_id = ?", [$user_id]);
    $cart_count = $cart_count_stmt->fetch()['count'];
} else {
    $session_id = get_session_id();
    $cart_count_stmt = db_query("SELECT COUNT(*) as count FROM cart WHERE session_id = ?", [$session_id]);
    $cart_count = $cart_count_stmt->fetch()['count'];
}

if ($cart_count == 0) {
    header('Location: shop.php');
    exit;
}

// Redirect to login if not logged in and guest checkout is disabled
if (!is_logged_in() && !ENABLE_GUEST_CHECKOUT) {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

$page_title = 'Checkout';
$error = '';
$success = '';

// Fetch cart items for summary
if (is_logged_in()) {
    $cart_items = db_fetch_all("
        SELECT c.*, p.name, p.price, p.discount_price, p.sku, pi.image_path
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        WHERE c.user_id = ?
    ", [$user_id]);
} else {
    $cart_items = db_fetch_all("
        SELECT c.*, p.name, p.price, p.discount_price, p.sku, pi.image_path
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        WHERE c.session_id = ?
    ", [$session_id]);
}

$subtotal = 0;
foreach ($cart_items as $item) {
    $price = $item['discount_price'] ?? $item['price'];
    $subtotal += $price * $item['quantity'];
}

$shipping_cost = calculate_shipping($subtotal);
$total_amount = $subtotal + $shipping_cost;

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $first_name = clean_input($_POST['first_name'] ?? '');
    $last_name = clean_input($_POST['last_name'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $address = clean_input($_POST['address'] ?? '');
    $city = clean_input($_POST['city'] ?? '');
    $state = clean_input($_POST['state'] ?? '');
    $zip = clean_input($_POST['zip'] ?? '');
    $payment_option = clean_input($_POST['payment_option'] ?? 'full'); // 'full' or 'advance'
    $payment_method = clean_input($_POST['payment_method'] ?? 'bank_transfer');

    // Calculate dynamic payment amounts based on selection
    if ($payment_option === 'advance') {
        $advance_payment = ceil($total_amount * 0.5); // 50%
        $remaining_payment = $total_amount - $advance_payment;
    } else {
        $advance_payment = $total_amount; // 100%
        $remaining_payment = 0;
    }

    if (empty($first_name) || empty($last_name) || empty($email) || empty($address) || empty($city)) {
        $error = "Please fill in all required shipping fields.";
    } else {
        try {
            db_begin_transaction();

            $order_number = generate_order_number();
            $shipping_name = $first_name . ' ' . $last_name;

            // Insert order
            $sql = "INSERT INTO orders (
                order_number, user_id, guest_email, shipping_name, shipping_phone,
                shipping_address_line1, shipping_city, shipping_state, shipping_postal_code,
                subtotal, shipping_cost, total_amount, advance_payment_amount, remaining_payment_amount,
                payment_method, order_status, payment_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $order_params = [
                $order_number,
                is_logged_in() ? $user_id : null,
                is_logged_in() ? null : $email,
                $shipping_name,
                $phone,
                $address,
                $city,
                $state,
                $zip,
                $subtotal,
                $shipping_cost,
                $total_amount,
                $advance_payment,
                $remaining_payment,
                $payment_method,
                ORDER_STATUS_PENDING,
                PAYMENT_STATUS_UNPAID
            ];

            db_query($sql, $order_params);
            $order_id = db_last_id();

            // Insert order items
            foreach ($cart_items as $item) {
                $price = $item['discount_price'] ?? $item['price'];
                db_query("INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)", [
                    $order_id,
                    $item['product_id'],
                    $item['name'],
                    $item['sku'],
                    $item['quantity'],
                    $price,
                    $price * $item['quantity']
                ]);
                
                // Update stock
                db_query("UPDATE products SET stock_quantity = stock_quantity - ?, sales_count = sales_count + ? WHERE product_id = ?", [
                    $item['quantity'], $item['quantity'], $item['product_id']
                ]);
            }

            // Clear cart
            if (is_logged_in()) {
                db_query("DELETE FROM cart WHERE user_id = ?", [$user_id]);
            } else {
                db_query("DELETE FROM cart WHERE session_id = ?", [$session_id]);
            }

            db_commit();
            header('Location: order-success.php?order=' . $order_number);
            exit;

        } catch (Exception $e) {
            db_rollback();
            $error = "Failed to place order: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<section class="section" style="padding: 4rem 0; background: var(--bg-secondary);">
    <div class="container">
        <h1 style="margin-bottom: 3rem; text-align: center; font-family: var(--font-secondary);">Complete Your Order</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom: 2rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" id="checkoutForm">
            <div class="row">
                <!-- Shipping Info -->
                <div class="col-7">
                    <div style="background: white; border-radius: 12px; padding: 2.5rem; box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
                        <h2 style="font-size: 1.5rem; margin-bottom: 2rem; border-bottom: 2px solid var(--bg-tertiary); padding-bottom: 1rem;">Shipping Information</h2>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" name="first_name" class="form-control" required value="<?php echo is_logged_in() ? $_SESSION['user_first_name'] ?? '' : ''; ?>">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" name="last_name" class="form-control" required value="<?php echo is_logged_in() ? $_SESSION['user_last_name'] ?? '' : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" name="email" class="form-control" required value="<?php echo is_logged_in() ? $_SESSION['user_email'] ?? '' : ''; ?>">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Phone Number *</label>
                                    <input type="text" name="phone" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Detailed Address *</label>
                            <input type="text" name="address" class="form-control" placeholder="House number, Street, Area/Sector" required>
                        </div>

                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="form-label">City *</label>
                                    <input type="text" name="city" class="form-control" placeholder="e.g. Lahore" required>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="form-label">Province *</label>
                                    <input type="text" name="state" class="form-control" placeholder="e.g. Punjab" required>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" name="zip" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Section -->
                    <div style="background: white; border-radius: 12px; padding: 2.5rem; box-shadow: var(--shadow-sm);">
                        <h2 style="font-size: 1.5rem; margin-bottom: 2rem; border-bottom: 2px solid var(--bg-tertiary); padding-bottom: 1rem;">Payment Method</h2>
                        
                        <div class="payment-options">
                            <label style="display: flex; align-items: flex-start; gap: 1rem; padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 10px; margin-bottom: 1rem; cursor: pointer;">
                                <input type="radio" name="payment_method" value="bank_transfer" style="margin-top: 0.25rem;" checked>
                                <div>
                                    <strong style="display: block; margin-bottom: 0.25rem;">Direct Bank Transfer</strong>
                                    <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0;">Transfer payment directly to our bank account. Use Order ID as reference.</p>
                                </div>
                            </label>

                            <!-- <label style="display: flex; align-items: flex-start; gap: 1rem; padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 10px; cursor: pointer;">
                                <input type="radio" name="payment_method" value="mobile_wallet" style="margin-top: 0.25rem;">
                                <div>
                                    <strong style="display: block; margin-bottom: 0.25rem;">Mobile Wallet (JazzCash/EasyPaisa)</strong>
                                    <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0;">Pay securely using your mobile wallet account.</p>
                                </div>
                            </label> -->
                        </div>
                    </div>
                </div>

                <!-- Order Summary Side -->
                <div class="col-5">
                    <div style="background: white; border-radius: 12px; padding: 2.5rem; box-shadow: var(--shadow-md); position: sticky; top: 120px;">
                        <h2 style="font-size: 1.5rem; margin-bottom: 2rem;">Your Order</h2>
                        
                        <div class="cart-summary-items" style="margin-bottom: 2rem;">
                            <?php foreach ($cart_items as $item): ?>
                                <?php $price = $item['discount_price'] ?? $item['price']; ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed var(--border-color);">
                                    <div style="display: flex; gap: 1rem; align-items: center;">
                                        <img src="<?php echo BASE_URL . ($item['image_path'] ?? 'assets/images/placeholder-product.jpg'); ?>" style="width: 50px; height: 50px; border-radius: 6px; object-fit: cover;">
                                        <div>
                                            <p style="margin: 0; font-weight: 600; font-size: 0.875rem;"><?php echo htmlspecialchars($item['name']); ?></p>
                                            <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted);">Qty: <?php echo $item['quantity']; ?></p>
                                        </div>
                                    </div>
                                    <span style="font-weight: 600; font-size: 0.875rem;"><?php echo format_price($price * $item['quantity']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="background: var(--bg-tertiary); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                                <span>Subtotal</span>
                                <span><?php echo format_price($subtotal); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                                <span>Shipping</span>
                                <span style="font-weight: 600;"><?php echo $shipping_cost == 0 ? 'FREE' : format_price($shipping_cost); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="font-size: 1.25rem;">Total</strong>
                                <strong style="font-size: 1.5rem; color: var(--primary-color);"><?php echo format_price($total_amount); ?></strong>
                            </div>
                        </div>

                        <!-- Payment Plan Selection -->
                        <div style="margin-bottom: 2rem;">
                            <h4 style="margin-bottom: 1rem; font-size: 1.1rem;">Choose Payment Plan</h4>
                            
                            <!-- Option 1: Full Payment -->
                            <label class="payment-plan-option" style="display: block; border: 2px solid var(--border-color); border-radius: 10px; padding: 1rem; margin-bottom: 1rem; cursor: pointer; transition: all 0.3s;" onclick="updatePaymentSummary('full')">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <input type="radio" name="payment_option" value="full" checked>
                                        <span style="font-weight: 600;">Full Payment (100%)</span>
                                    </div>
                                    <span class="badge badge-success" style="font-size: 0.75rem;">Recommended</span>
                                </div>
                            </label>

                            <!-- Option 2: Advance Payment -->
                            <label class="payment-plan-option" style="display: block; border: 2px solid var(--border-color); border-radius: 10px; padding: 1rem; cursor: pointer; transition: all 0.3s;" onclick="updatePaymentSummary('advance')">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <input type="radio" name="payment_option" value="advance">
                                    <div>
                                        <span style="font-weight: 600; display: block;">Advance Payment (50%)</span>
                                        <span style="font-size: 0.8rem; color: var(--text-muted);">Pay 50% now, remaining on delivery</span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Dynamic Payment Summary -->
                        <div id="paymentBreakdown" style="background: rgba(139, 111, 71, 0.1); border: 1px solid var(--primary-color); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
                            <h4 style="color: var(--primary-color); margin-top: 0; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; font-size: 1rem;">
                                <i class="fas fa-wallet"></i> Payment Summary
                            </h4>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-weight: 700; font-size: 1.1rem;">
                                <span>Pay Now:</span>
                                <span id="payNowAmount"><?php echo format_price($total_amount); ?></span>
                            </div>
                            <div id="remainingRow" style="display: none; justify-content: space-between; font-size: 0.9rem; color: var(--text-secondary); margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed var(--primary-color);">
                                <span>Pay on Delivery:</span>
                                <span id="payLaterAmount"><?php echo format_price(0); ?></span>
                            </div>
                        </div>

                        <button type="submit" name="place_order" class="btn btn-primary btn-lg btn-block" style="padding: 1.25rem;">
                            Place Order <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                        </button>
                        
                        <p style="text-align: center; font-size: 0.75rem; color: var(--text-muted); margin-top: 1.5rem; line-height: 1.5;">
                            By placing an order, you agree to our terms and conditions. Your payment information is securely processed.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- JavaScript for Dynamic Payment Updates -->
<script>
function updatePaymentSummary(type) {
    const totalAmount = <?php echo $total_amount; ?>;
    const payNowEl = document.getElementById('payNowAmount');
    const payLaterEl = document.getElementById('payLaterAmount');
    const remainingRow = document.getElementById('remainingRow');
    const options = document.querySelectorAll('.payment-plan-option');

    // Reset styles
    options.forEach(opt => {
        opt.style.borderColor = 'var(--border-color)';
        opt.style.backgroundColor = 'transparent';
    });

    if (type === 'advance') {
        // 50% Logic
        const advance = Math.ceil(totalAmount * 0.5);
        const remaining = totalAmount - advance;

        payNowEl.textContent = 'Rs. ' + advance.toLocaleString();
        payLaterEl.textContent = 'Rs. ' + remaining.toLocaleString();
        remainingRow.style.display = 'flex';
        
        // Highlight active option
        document.querySelector('input[value="advance"]').closest('.payment-plan-option').style.borderColor = 'var(--primary-color)';
        document.querySelector('input[value="advance"]').closest('.payment-plan-option').style.backgroundColor = 'rgba(139, 111, 71, 0.05)';

    } else {
        // Full Payment Logic
        payNowEl.textContent = 'Rs. ' + totalAmount.toLocaleString();
        remainingRow.style.display = 'none';

        // Highlight active option
        document.querySelector('input[value="full"]').closest('.payment-plan-option').style.borderColor = 'var(--primary-color)';
        document.querySelector('input[value="full"]').closest('.payment-plan-option').style.backgroundColor = 'rgba(139, 111, 71, 0.05)';
    }
}

// Initialize default state (Full Payment)
document.addEventListener('DOMContentLoaded', () => {
    updatePaymentSummary('full');
});
</script>

<?php include 'includes/footer.php'; ?>
