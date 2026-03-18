<?php
/**
 * ============================================
 * ORDER SUCCESS PAGE
 * ============================================
 */

require_once 'config/config.php';

$order_number = $_GET['order'] ?? '';

if (empty($order_number)) {
    header('Location: index.php');
    exit;
}

// Fetch order details
$order = db_fetch("SELECT * FROM orders WHERE order_number = ?", [$order_number]);

if (!$order) {
    header('Location: index.php');
    exit;
}

$page_title = 'Order Confirmed';
include 'includes/header.php';
?>

<section class="section" style="padding: 6rem 0; text-align: center; background: var(--bg-secondary);">
    <div class="container">
        <div style="max-width: 700px; margin: 0 auto; background: white; padding: 4rem; border-radius: 20px; box-shadow: var(--shadow-lg);">
            <div style="width: 80px; height: 80px; background: var(--success); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; font-size: 2.5rem;">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 style="font-family: var(--font-secondary); font-size: 2.5rem; margin-bottom: 1rem;">Order Confirmed!</h1>
            <p style="color: var(--text-secondary); font-size: 1.125rem; margin-bottom: 3rem;">
                Thank you for your purchase. Your order <strong>#<?php echo htmlspecialchars($order_number); ?></strong> has been placed successfully.
            </p>

            <div style="text-align: left; background: var(--bg-tertiary); padding: 2rem; border-radius: 12px; margin-bottom: 3rem;">
                <h3 style="font-size: 1.125rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; color: var(--primary-color);">
                    <i class="fas fa-money-bill-wave"></i> Payment Instructions
                </h3>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                    <?php if ($order['remaining_payment_amount'] > 0): ?>
                        <span>Advance Amount Due (50%):</span>
                        <strong style="font-size: 1.25rem;"><?php echo format_price($order['advance_payment_amount']); ?></strong>
                    <?php else: ?>
                        <span>Total Amount Due (100%):</span>
                        <strong style="font-size: 1.25rem;"><?php echo format_price($order['total_amount']); ?></strong>
                    <?php endif; ?>
                </div>

                <p style="font-size: 0.875rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 1.5rem;">
                    <?php 
                        $amount_type = ($order['remaining_payment_amount'] > 0) ? "advance amount" : "total amount";
                    ?>
                    <?php if ($order['payment_method'] === 'bank_transfer'): ?>
                        Please transfer the <?php echo $amount_type; ?> to the following bank account to confirm your order:
                        <br><br>
                        <strong>Bank:</strong> Meezan Bank<br>
                        <strong>Account Name:</strong> SANA SIDDIQUI<br>
                        <strong>Account Number:</strong> 0152-0104-0513-14<br>
                        <strong>IBAN:</strong> PK46MEZN0001520104051314
                    <?php else: ?>
                        Please pay the <?php echo $amount_type; ?> using JazzCash or EasyPaisa:
                        <br><br>
                        <strong>JazzCash/EasyPaisa:</strong> 0300-1234567<br>
                        <strong>Name:</strong> Vintage Craft Shop
                    <?php endif; ?>
                </p>

                <div style="background: rgba(122, 155, 118, 0.1); border-left: 4px solid var(--success); padding: 1rem; margin-bottom: 1rem;">
                    <p style="font-size: 0.875rem; margin: 0; color: #4a5d47;">
                        <i class="fas fa-info-circle"></i> After payment, please reply to your confirmation email with the transaction screenshot to process your order.
                    </p>
                </div>

                <div style="background: rgba(183, 156, 117, 0.1); border-left: 4px solid var(--warning); padding: 1rem;">
                    <p style="font-size: 0.875rem; margin: 0; color: #8b6f47;">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Note:</strong> Once an order has been placed, the payment will be non-refundable.
                    </p>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="<?php echo BASE_URL; ?>account.php" class="btn btn-outline" style="flex: 1;">
                    <i class="fas fa-box"></i> View Order Status
                </a>
                <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
