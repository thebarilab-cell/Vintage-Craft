<?php
/**
 * ============================================
 * PRODUCT DETAILS PAGE
 * ============================================
 */

require_once 'config/config.php';

// Get product slug
$slug = clean_input($_GET['slug'] ?? '');

if (empty($slug)) {
    header('Location: shop.php');
    exit;
}

// Fetch product with category info
$product = db_fetch("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.slug = ? AND p.is_active = 1
", [$slug]);

if (!$product) {
    // If not found, try searching by ID as fallback
    if (is_numeric($slug)) {
        $product = db_fetch("
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            WHERE p.product_id = ? AND p.is_active = 1
        ", [$slug]);
    }
}

if (!$product) {
    // Redirect to 404 or shop
    header('Location: shop.php');
    exit;
}

// Fetch product images
$product_images = db_fetch_all("
    SELECT * FROM product_images 
    WHERE product_id = ? 
    ORDER BY is_primary DESC, display_order ASC
", [$product['product_id']]);

// Fetch related products (same category)
$related_products = db_fetch_all("
    SELECT p.*, c.name as category_name, pi.image_path
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    WHERE p.category_id = ? AND p.product_id != ? AND p.is_active = 1
    LIMIT 4
", [$product['category_id'], $product['product_id']]);

// Fetch reviews
$reviews = db_fetch_all("
    SELECT r.*, u.first_name, u.last_name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.product_id = ? AND r.is_approved = 1
    ORDER BY r.created_at DESC
", [$product['product_id']]);

// Increment view count
db_query("UPDATE products SET views = views + 1 WHERE product_id = ?", [$product['product_id']]);

// Page meta
$page_title = $product['meta_title'] ?? $product['name'] . ' - ' . SITE_NAME;
$meta_description = $product['meta_description'] ?? $product['short_description'];

include 'includes/header.php';
?>

<div class="breadcrumb-container" style="background: var(--bg-tertiary); padding: 1.5rem 0; border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; gap: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">
            <li><a href="index.php" style="color: inherit; text-decoration: none;">Home</a> <i class="fas fa-chevron-right" style="font-size: 0.65rem; margin-top: 3px;"></i></li>
            <li><a href="shop.php" style="color: inherit; text-decoration: none;">Shop</a> <i class="fas fa-chevron-right" style="font-size: 0.65rem; margin-top: 3px;"></i></li>
            <li><a href="categories.php?category=<?php echo $product['category_slug']; ?>" style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($product['category_name']); ?></a> <i class="fas fa-chevron-right" style="font-size: 0.65rem; margin-top: 3px;"></i></li>
            <li style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($product['name']); ?></li>
        </ul>
    </div>
</div>

<section class="section" style="padding: 4rem 0;">
    <div class="container">
        <div class="row">
            <!-- Product Images -->
            <div class="col-6">
                <div class="product-gallery" style="position: sticky; top: 120px;">
                    <!-- Main Image -->
                    <div class="main-image-preview" style="background: white; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; aspect-ratio: 1/1;">
                        <img id="mainImage" src="<?php echo BASE_URL . ($product_images[0]['image_path'] ?? 'assets/images/placeholder-product.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
                    </div>
                    
                    <!-- Thumbnails -->
                    <?php if (count($product_images) > 1): ?>
                        <div class="thumbnail-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                            <?php foreach ($product_images as $img): ?>
                                <div class="thumb-item" 
                                     style="cursor: pointer; border-radius: 8px; overflow: hidden; border: 2px solid transparent; transition: all 0.3s;"
                                     onclick="document.getElementById('mainImage').src = '<?php echo BASE_URL . $img['image_path']; ?>'; this.parentElement.querySelectorAll('.thumb-item').forEach(el => el.style.borderColor = 'transparent'); this.style.borderColor = 'var(--primary-color)';"
                                >
                                    <img src="<?php echo BASE_URL . $img['image_path']; ?>" style="width: 100%; aspect-ratio: 1/1; object-fit: cover;">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Details -->
            <div class="col-6">
                <div class="product-details-content" style="padding-left: 2rem;">
                    <span class="badge" style="background: var(--bg-tertiary); color: var(--primary-color); font-weight: 600; font-size: 0.75rem; letter-spacing: 1px; margin-bottom: 1rem; display: inline-block; padding: 0.4rem 0.8rem;">
                        <?php echo strtoupper($product['category_name']); ?>
                    </span>
                    
                    <h1 style="font-size: 2.75rem; margin-bottom: 1rem; line-height: 1.2;"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                        <div class="product-rating" style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="color: #FBBF24;">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                            </div>
                            <span style="font-size: 0.875rem; color: var(--text-muted);">(<?php echo count($reviews); ?> Reviews)</span>
                        </div>
                        <span style="color: var(--border-color); font-weight: 300;">|</span>
                        <div style="font-size: 0.875rem; color: var(--text-muted);">
                            SKU: <span style="color: var(--text-primary); font-weight: 600;"><?php echo htmlspecialchars($product['sku']); ?></span>
                        </div>
                    </div>

                    <div class="price-box" style="margin-bottom: 2rem;">
                        <?php if ($product['discount_price']): ?>
                            <span style="font-size: 2rem; font-weight: 700; color: var(--primary-color);"><?php echo format_price($product['discount_price']); ?></span>
                            <span style="font-size: 1.25rem; text-decoration: line-through; color: var(--text-muted); margin-left: 1rem;"><?php echo format_price($product['price']); ?></span>
                        <?php else: ?>
                            <span style="font-size: 2rem; font-weight: 700; color: var(--text-primary);"><?php echo format_price($product['price']); ?></span>
                        <?php endif; ?>
                        
                        <div style="margin-top: 0.5rem;">
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <span style="color: var(--success); font-weight: 600; font-size: 0.875rem;">
                                    <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock_quantity']; ?> available)
                                </span>
                            <?php else: ?>
                                <span style="color: var(--danger); font-weight: 600; font-size: 0.875rem;">
                                    <i class="fas fa-times-circle"></i> Out of Stock
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <p style="color: var(--text-secondary); line-height: 1.8; margin-bottom: 2.5rem; font-size: 1.125rem;">
                        <?php echo htmlspecialchars($product['short_description']); ?>
                    </p>

                    <!-- Add to Cart Form -->
                    <form id="addToCartForm" style="margin-bottom: 3rem; background: var(--bg-tertiary); padding: 2rem; border-radius: 12px;">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        
                        <div style="display: flex; gap: 1rem; align-items: flex-end;">
                            <div class="form-group" style="margin: 0; flex: 0 0 120px;">
                                <label class="form-label" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Quantity</label>
                                <div style="display: flex; border: 2px solid var(--border-color); border-radius: var(--radius-md); background: white; overflow: hidden;">
                                    <button type="button" onclick="const qty = document.getElementById('qtyInput'); if(qty.value > 1) qty.value--; updateQtyValue();" 
                                            style="width: 40px; border: none; background: none; cursor: pointer; color: var(--text-muted); padding: 0.5rem;"><i class="fas fa-minus"></i></button>
                                    <input type="number" id="qtyInput" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" 
                                           style="width: 40px; border: none; text-align: center; font-weight: 600; padding: 0.5rem; -moz-appearance: textfield;">
                                    <button type="button" onclick="const qty = document.getElementById('qtyInput'); if(qty.value < <?php echo $product['stock_quantity']; ?>) qty.value++; updateQtyValue();"
                                            style="width: 40px; border: none; background: none; cursor: pointer; color: var(--text-muted); padding: 0.5rem;"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            
                            <div style="flex: 1;">
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <button type="submit" class="btn btn-primary btn-lg btn-block add-to-cart-btn" 
                                            data-product-id="<?php echo $product['product_id']; ?>" 
                                            data-quantity="1"
                                            style="padding: 1rem 0; height: 100%;">
                                        <i class="fas fa-shopping-bag" style="margin-right: 0.5rem;"></i> Add to Cart
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-outline btn-lg btn-block" disabled>Notify When Available</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color); display: flex; gap: 2rem;">
                            <a href="#" class="wishlist-btn" data-product-id="<?php echo $product['product_id']; ?>" style="color: var(--text-secondary); text-decoration: none; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="far fa-heart"></i> Add to Wishlist
                            </a>
                            <a href="#" style="color: var(--text-secondary); text-decoration: none; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-share-alt"></i> Share Product
                            </a>
                        </div>
                    </form>

                    <!-- Trust Checklist -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <!-- <div style="display: flex; gap: 0.75rem; align-items: center;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(139, 111, 71, 0.1); color: var(--primary-color); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-truck" style="font-size: 0.875rem;"></i>
                            </div>
                            <span style="font-size: 0.815rem; color: var(--text-secondary);">Free Global Shipping above $100</span>
                        </div> -->
                        <div style="display: flex; gap: 0.75rem; align-items: center;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(139, 111, 71, 0.1); color: var(--primary-color); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-leaf" style="font-size: 0.875rem;"></i>
                            </div>
                            <span style="font-size: 0.815rem; color: var(--text-secondary);">Eco-conscious & Sustainable</span>
                        </div>
                        <!-- <div style="display: flex; gap: 0.75rem; align-items: center;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(139, 111, 71, 0.1); color: var(--primary-color); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-undo" style="font-size: 0.875rem;"></i>
                            </div>
                            <span style="font-size: 0.815rem; color: var(--text-secondary);">30-Day Hassle Free Returns</span>
                        </div> -->
                        <div style="display: flex; gap: 0.75rem; align-items: center;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(139, 111, 71, 0.1); color: var(--primary-color); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-shield-alt" style="font-size: 0.875rem;"></i>
                            </div>
                            <span style="font-size: 0.815rem; color: var(--text-secondary);">Best Quality Products</span>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tabs Section (Description, Specs, Reviews) -->
<section style="padding: 4rem 0; border-top: 1px solid var(--border-color); background: var(--bg-secondary);">
    <div class="container">
        <div class="tabs-wrapper" style="max-width: 900px; margin: 0 auto; background: white; border-radius: 20px; box-shadow: var(--shadow-sm); overflow: hidden;">
            <div class="tab-header" style="display: flex; border-bottom: 1px solid var(--border-color); background: var(--bg-tertiary);">
                <button onclick="switchTab('desc')" class="tab-btn active" id="btn-desc" style="flex: 1; padding: 1.5rem; border: none; background: none; cursor: pointer; font-weight: 600; font-family: var(--font-secondary); font-size: 1.125rem;">Description</button>
                <button onclick="switchTab('reviews')" class="tab-btn" id="btn-reviews" style="flex: 1; padding: 1.5rem; border: none; background: none; cursor: pointer; font-weight: 600; font-family: var(--font-secondary); font-size: 1.125rem;">Reviews (<?php echo count($reviews); ?>)</button>
            </div>
            
            <div id="tab-desc" class="tab-content" style="padding: 3rem;">
                <h3 style="margin-bottom: 1.5rem; font-family: var(--font-secondary);">The Story Behind <?php echo htmlspecialchars($product['name']); ?></h3>
                <div style="line-height: 2; color: var(--text-secondary);">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
            </div>
            
            <div id="tab-reviews" class="tab-content" style="padding: 3rem; display: none;">
                    <div>
                        <h3 style="margin: 0; font-family: var(--font-secondary);">Customer Experiences</h3>
                        <p style="color: var(--text-muted); margin-top: 0.5rem;">Showing <?php echo count($reviews); ?> verified stories</p>
                    </div>
                    <?php if (is_logged_in()): ?>
                        <button onclick="document.getElementById('reviewFormWrapper').style.display='block'; this.style.display='none';" class="btn btn-outline btn-sm">Write Review</button>
                    <?php else: ?>
                        <a href="login.php?redirect=product.php?slug=<?php echo $product['slug']; ?>" class="btn btn-outline btn-sm">Login to Review</a>
                    <?php endif; ?>
                </div>

                <!-- Review Form -->
                <div id="reviewFormWrapper" style="display: none; background: var(--bg-tertiary); padding: 2.5rem; border-radius: 12px; margin-bottom: 4rem;">
                    <h4 style="margin-top: 0; margin-bottom: 1.5rem; font-family: var(--font-secondary);">Share Your Experience</h4>
                    <form id="submitReviewForm">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label class="form-label">Your Rating</label>
                            <div class="star-rating-input" style="display: flex; gap: 0.5rem; font-size: 1.5rem; color: #ddd; cursor: pointer;">
                                <i class="far fa-star" data-rating="1"></i>
                                <i class="far fa-star" data-rating="2"></i>
                                <i class="far fa-star" data-rating="3"></i>
                                <i class="far fa-star" data-rating="4"></i>
                                <i class="far fa-star" data-rating="5"></i>
                                <input type="hidden" name="rating" id="ratingInput" value="0">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Headline</label>
                            <input type="text" name="title" class="form-control" placeholder="Summarize your review in a few words" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Your Comment</label>
                            <textarea name="comment" class="form-control" rows="5" placeholder="What did you like or dislike? How was the craftsmanship?" required></textarea>
                        </div>

                        <div id="reviewMsg" style="margin-bottom: 1rem; display: none;"></div>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                            <button type="button" onclick="document.getElementById('reviewFormWrapper').style.display='none'; document.querySelector('.btn-outline.btn-sm').style.display='block';" class="btn btn-outline">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <?php if (!empty($reviews)): ?>
                    <div style="display: flex; flex-direction: column; gap: 2.5rem;">
                        <?php foreach ($reviews as $review): ?>
                            <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 2.5rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                    <div style="display:flex; gap: 1rem; align-items: center;">
                                        <div style="width: 45px; height: 45px; background: var(--bg-tertiary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--primary-color);">
                                            <?php echo substr($review['first_name'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <strong style="display: block;"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></strong>
                                            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem;">
                                                <span style="color: var(--text-muted);"><?php echo date('F d, Y', strtotime($review['created_at'])); ?></span>
                                                <?php if($review['is_verified_purchase']): ?>
                                                    <span style="color: #28a745; font-weight: 700; display: flex; align-items: center; gap: 2px;">
                                                        <i class="fas fa-check-circle" style="font-size: 0.7rem;"></i> Verified Purchase
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="color: #FBBF24; font-size: 0.815rem;">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="fa<?php echo ($i <= $review['rating']) ? 's' : 'r'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <h4 style="margin-bottom: 0.75rem; font-family: var(--font-secondary);"><?php echo htmlspecialchars($review['title'] ?: 'Wonderful Item'); ?></h4>
                                <p style="color: var(--text-secondary); line-height: 1.8; font-size: 0.9375rem;"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        <i class="far fa-comment-dots" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.4;"></i>
                        No reviews yet. Be the first to share your experience!
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (!empty($related_products)): ?>
    <section class="section" style="padding: 6rem 0;">
        <div class="container">
            <div style="text-align: center; margin-bottom: 4rem;">
                <h2 style="font-family: var(--font-secondary); font-size: 2.5rem; margin-bottom: 1rem;">You May Also Love</h2>
                <p style="color: var(--text-secondary);">Curated pieces from the same collection</p>
                <div style="width: 80px; height: 3px; background: var(--primary-color); margin: 2rem auto 0;"></div>
            </div>
            
            <?php 
            include_once 'includes/product-card.php';
            render_product_grid($related_products, 4); 
            ?>
        </div>
    </section>
<?php endif; ?>

<style>
    .tab-btn { transition: all 0.3s; opacity: 0.6; }
    .tab-btn.active { opacity: 1; border-bottom: 3px solid var(--primary-color); color: var(--primary-color); }
    .thumb-item img:hover { transform: scale(1.05); }
    
    @media (max-width: 991px) {
        .col-6 { flex: 0 0 100%; max-width: 100%; }
        .product-details-content { padding-left: 0; margin-top: 3rem; }
    }
</style>

<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        
        document.getElementById('tab-' + tabId).style.display = 'block';
        document.getElementById('btn-' + tabId).classList.add('active');
    }

    // Sync quantity input with button data attribute
    const qtyInput = document.getElementById('qtyInput');
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    
    window.updateQtyValue = function() {
        if (qtyInput && addToCartBtn) {
            addToCartBtn.dataset.quantity = qtyInput.value;
        }
    }
    
    if (qtyInput) {
        qtyInput.addEventListener('change', updateQtyValue);
    }
    
    // Handle form submit (prevent default since we use AJAX in main.js)
    const cartForm = document.getElementById('addToCartForm');
    if (cartForm) {
        cartForm.addEventListener('submit', function(e) {
            e.preventDefault();
        });
    }

    // Review Star Rating Interaction
    const stars = document.querySelectorAll('.star-rating-input i');
    const ratingInput = document.getElementById('ratingInput');

    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const rating = this.dataset.rating;
            highlightStars(rating);
        });

        star.addEventListener('mouseout', function() {
            highlightStars(ratingInput.value);
        });

        star.addEventListener('click', function() {
            ratingInput.value = this.dataset.rating;
            highlightStars(ratingInput.value);
        });
    });

    function highlightStars(rating) {
        stars.forEach(s => {
            if (s.dataset.rating <= rating) {
                s.classList.remove('far');
                s.classList.add('fas');
                s.style.color = '#FBBF24';
            } else {
                s.classList.remove('fas');
                s.classList.add('far');
                s.style.color = '#ddd';
            }
        });
    }

    // Submit Review AJAX
    const reviewForm = document.getElementById('submitReviewForm');
    const reviewMsg = document.getElementById('reviewMsg');

    if (reviewForm) {
        reviewForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
            try {
                const formData = new FormData(this);
                const response = await fetch('modules/reviews/submit.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                reviewMsg.style.display = 'block';
                if (data.success) {
                    reviewMsg.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    reviewForm.reset();
                    ratingInput.value = 0;
                    highlightStars(0);
                    setTimeout(() => {
                        document.getElementById('reviewFormWrapper').style.display = 'none';
                        document.querySelector('.btn-outline.btn-sm').style.display = 'block';
                    }, 3000);
                } else {
                    reviewMsg.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            } catch (error) {
                reviewMsg.style.display = 'block';
                reviewMsg.innerHTML = `<div class="alert alert-danger">An error occurred. Please try again.</div>`;
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Review';
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
