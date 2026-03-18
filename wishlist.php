<?php
/**
 * ============================================
 * WISHLIST PAGE
 * ============================================
 */

require_once 'config/config.php';
require_once 'includes/product-card.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php?redirect=wishlist.php');
    exit;
}

$page_title = 'My Wishlist';
include 'includes/header.php';

$user_id = get_user_id();

// Fetch wishlist items
$wishlist_items = db_fetch_all("
    SELECT p.*, c.name as category_name, pi.image_path,
           AVG(r.rating) as avg_rating, COUNT(DISTINCT r.review_id) as review_count
    FROM wishlist w
    JOIN products p ON w.product_id = p.product_id
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    LEFT JOIN reviews r ON p.product_id = r.product_id AND r.is_approved = 1
    WHERE w.user_id = ? AND p.is_active = 1
    GROUP BY p.product_id
    ORDER BY w.created_at DESC
", [$user_id]);
?>

<section style="background: var(--bg-tertiary); padding: 3rem 0; text-align: center; border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">My Wishlist</h1>
        <p style="color: var(--text-secondary);">Treasures you've saved for later</p>
    </div>
</section>

<section class="section" style="padding: 5rem 0;">
    <div class="container">
        <?php if (!empty($wishlist_items)): ?>
            <div class="row">
                <?php foreach ($wishlist_items as $product): ?>
                    <div class="col-3" style="margin-bottom: 2.5rem; position: relative;">
                        <!-- Remove from Wishlist button -->
                        <button onclick="removeFromWishlist(<?php echo $product['product_id']; ?>, this)" 
                                style="position: absolute; top: 1rem; right: 2rem; z-index: 10; background: white; border: none; width: 32px; height: 32px; border-radius: 50%; box-shadow: var(--shadow-sm); cursor: pointer; color: var(--danger); transition: all 0.3s;"
                                title="Remove from Wishlist">
                            <i class="fas fa-times"></i>
                        </button>
                        <?php render_product_card($product); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 6rem 0; border: 2px dashed var(--border-color); border-radius: 20px;">
                <div style="width: 100px; height: 100px; background: var(--bg-tertiary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
                    <i class="far fa-heart" style="font-size: 3rem; color: var(--text-muted);"></i>
                </div>
                <h2 style="font-size: 1.75rem; margin-bottom: 1rem;">Your Wishlist is Empty</h2>
                <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto 2.5rem;">Browse our collection and save your favorite pieces to find them easily later.</p>
                <a href="shop.php" class="btn btn-primary btn-lg">Explore Collections</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Related Products (Featured) -->
<section style="padding: 6rem 0; border-top: 1px solid var(--border-color); background: var(--bg-secondary);">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 4rem;">
            <div>
                <h2 style="font-family: var(--font-secondary); font-size: 2.25rem; margin: 0;">Featured Treasures</h2>
                <p style="color: var(--text-secondary); margin-top: 0.5rem;">Explore our most loved handcrafted pieces</p>
            </div>
            <a href="shop.php?filter=featured" class="btn btn-outline btn-sm">View All Featured</a>
        </div>
        
        <?php 
        $featured = db_fetch_all("
            SELECT p.*, c.name as category_name, pi.image_path
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
            WHERE p.is_featured = 1 AND p.is_active = 1
            LIMIT 4
        ");
        render_product_grid($featured, 4);
        ?>
    </div>
</section>

<script>
    async function removeFromWishlist(productId, btn) {
        if (!confirm('Remove this item from your wishlist?')) return;
        
        try {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('action', 'remove');
            
            const response = await fetch('modules/wishlist/toggle.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                // If it was the last item, reload to show empty state
                const remaining = document.querySelectorAll('.col-3').length;
                if (remaining <= 4) { // Only the related products + this one
                     location.reload();
                } else {
                    btn.closest('.col-3').remove();
                    showNotification('Item removed from wishlist', 'info');
                }
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
