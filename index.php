<?php
/**
 * ============================================
 * HOMEPAGE - Vintage Craft
 * ============================================
 */

require_once 'config/config.php';
require_once 'includes/product-card.php';

// Page metadata
$page_title = 'Home';
$meta_description = 'Discover unique handcrafted products and vintage treasures at Vintage Craft. Shop artisan goods for your home.';

// Fetch featured products
$featured_products = db_fetch_all("
    SELECT p.*, c.name as category_name, pi.image_path,
           AVG(r.rating) as avg_rating, COUNT(DISTINCT r.review_id) as review_count
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    LEFT JOIN reviews r ON p.product_id = r.product_id AND r.is_approved = 1
    WHERE p.is_active = 1 AND p.is_featured = 1
    GROUP BY p.product_id
    ORDER BY p.created_at DESC
    LIMIT 8
");

// Fetch new arrivals
$new_products = db_fetch_all("
    SELECT p.*, c.name as category_name, pi.image_path,
           AVG(r.rating) as avg_rating, COUNT(DISTINCT r.review_id) as review_count
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    LEFT JOIN reviews r ON p.product_id = r.product_id AND r.is_approved = 1
    WHERE p.is_active = 1 AND p.is_new = 1
    GROUP BY p.product_id
    ORDER BY p.created_at DESC
    LIMIT 8
");

// Fetch categories
$categories = db_fetch_all("
    SELECT category_id, name, slug, description, image
    FROM categories
    WHERE is_active = 1
    ORDER BY display_order ASC
    LIMIT 6
");

// Include header
include 'includes/header.php';
?>

<!-- ============================================
     HERO SECTION
     ============================================ -->
<section class="hero-section" style="background: linear-gradient(135deg, var(--cream) 0%, var(--beige) 100%); padding: 4rem 0;">
    <div class="container">
        <div class="row align-center">
            <div class="col-6">
                <div class="hero-content" style="padding-right: 2rem;">
                    <h1 style="font-size: 3.5rem; margin-bottom: 1.5rem; line-height: 1.1;">
                        Discover Handcrafted
                        <span style="color: var(--primary-color); display: block;">Treasures</span>
                    </h1>
                    <p style="font-size: 1.25rem; color: var(--text-secondary); margin-bottom: 2rem; line-height: 1.6;">
                        Unique artisan products, vintage finds, and handmade crafts for your home. 
                        Each piece tells a story.
                    </p>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag"></i> Shop Now
                        </a>
                        <a href="<?php echo BASE_URL; ?>categories.php" class="btn btn-outline btn-lg">
                            <i class="fas fa-th-large"></i> Browse Categories
                        </a>
                    </div>
                    
                    <!-- Features -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 3rem;">
                        <div style="text-align: center;">
                            <i class="fas fa-truck" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;"></i>
                            <p style="font-size: 0.875rem; font-weight: 600; margin: 0;">Free Shipping</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">On orders Rs. 5,000+</p>
                        </div>
                        <div style="text-align: center;">
                            <i class="fas fa-hand-holding-heart" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;"></i>
                            <p style="font-size: 0.875rem; font-weight: 600; margin: 0;">Handcrafted</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">100% Authentic</p>
                        </div>
                        <div style="text-align: center;">
                            <i class="fas fa-shield-alt" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;"></i>
                            <p style="font-size: 0.875rem; font-weight: 600; margin: 0;">Secure Payment</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">50% Advance</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="hero-image" style="position: relative;">
                    <img 
                        src="<?php echo ASSETS_URL; ?>images/hero-image.jpg" 
                        alt="Handcrafted Products"
                        style="width: 100%; border-radius: 16px; box-shadow: var(--shadow-xl);"
                        onerror="this.src='https://images.unsplash.com/photo-1513519245088-0e12902e35ca?w=800&h=600&fit=crop'"
                    >
                    <!-- Floating badge -->
                    <div style="position: absolute; top: 20px; right: 20px; background: white; padding: 1rem 1.5rem; border-radius: 12px; box-shadow: var(--shadow-lg);">
                        <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0;">Special Offer</p>
                        <p style="font-size: 1.5rem; font-weight: 700; color: var(--terracotta); margin: 0;">50% Advance</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     CATEGORIES SECTION
     ============================================ -->
<section class="section">
    <div class="container">
        <div class="text-center" style="margin-bottom: 3rem;">
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Shop by Category</h2>
            <p style="color: var(--text-secondary); font-size: 1.125rem;">Explore our curated collections</p>
        </div>
        
        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-4" style="margin-bottom: 2rem;">
                    <a href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $category['slug']; ?>" 
                       class="category-card" 
                       style="display: block; text-decoration: none;">
                        <div style="background: var(--bg-primary); border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-sm); transition: all 0.3s ease; height: 100%;">
                            <div style="position: relative; padding-top: 75%; overflow: hidden;">
                                <img 
                                    src="<?php echo $category['image'] ? BASE_URL . $category['image'] : 'https://images.unsplash.com/photo-1513519245088-0e12902e35ca?w=400&h=300&fit=crop'; ?>"
                                    alt="<?php echo htmlspecialchars($category['name']); ?>"
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                                    onmouseover="this.style.transform='scale(1.1)'"
                                    onmouseout="this.style.transform='scale(1)'"
                                >
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); padding: 1.5rem;">
                                    <h3 style="color: white; font-size: 1.5rem; margin: 0;"><?php echo htmlspecialchars($category['name']); ?></h3>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center" style="margin-top: 2rem;">
            <a href="<?php echo BASE_URL; ?>categories.php" class="btn btn-outline">
                View All Categories <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     FEATURED PRODUCTS
     ============================================ -->
<?php if (!empty($featured_products)): ?>
<section class="section" style="background: var(--bg-tertiary);">
    <div class="container">
        <div class="text-center" style="margin-bottom: 3rem;">
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Featured Products</h2>
            <p style="color: var(--text-secondary); font-size: 1.125rem;">Handpicked favorites from our collection</p>
        </div>
        
        <?php render_product_grid($featured_products, 4); ?>
        
        <div class="text-center" style="margin-top: 2rem;">
            <a href="<?php echo BASE_URL; ?>shop.php?filter=featured" class="btn btn-primary">
                View All Featured <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================
     NEW ARRIVALS
     ============================================ -->
<?php if (!empty($new_products)): ?>
<section class="section">
    <div class="container">
        <div class="text-center" style="margin-bottom: 3rem;">
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;">New Arrivals</h2>
            <p style="color: var(--text-secondary); font-size: 1.125rem;">Fresh finds just added to our store</p>
        </div>
        
        <?php render_product_grid($new_products, 4); ?>
        
        <div class="text-center" style="margin-top: 2rem;">
            <a href="<?php echo BASE_URL; ?>shop.php?filter=new" class="btn btn-primary">
                View All New Products <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================
     WHY CHOOSE US
     ============================================ -->
<section class="section" style="background: var(--primary-color); color: white;">
    <div class="container">
        <div class="text-center" style="margin-bottom: 3rem;">
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem; color: white;">Why Choose Vintage Craft?</h2>
            <p style="color: var(--beige); font-size: 1.125rem;">Quality, authenticity, and care in every piece</p>
        </div>
        
        <div class="row">
            <div class="col-3">
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-hand-holding-heart" style="font-size: 3.5rem; margin-bottom: 1rem; color: var(--accent-color);"></i>
                    <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: white;">Handcrafted Quality</h3>
                    <p style="color: var(--beige); line-height: 1.6;">Every product is carefully crafted by skilled artisans with attention to detail.</p>
                </div>
            </div>
            <div class="col-3">
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-certificate" style="font-size: 3.5rem; margin-bottom: 1rem; color: var(--accent-color);"></i>
                    <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: white;">100% Authentic</h3>
                    <p style="color: var(--beige); line-height: 1.6;">Genuine vintage items and authentic handmade products guaranteed.</p>
                </div>
            </div>
            <div class="col-3">
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-credit-card" style="font-size: 3.5rem; margin-bottom: 1rem; color: var(--accent-color);"></i>
                    <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: white;">Flexible Payment</h3>
                    <p style="color: var(--beige); line-height: 1.6;">Pay 50% now and 50% on delivery. Shop with confidence!</p>
                </div>
            </div>
            <div class="col-3">
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-headset" style="font-size: 3.5rem; margin-bottom: 1rem; color: var(--accent-color);"></i>
                    <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: white;">Customer Support</h3>
                    <p style="color: var(--beige); line-height: 1.6;">Dedicated support team ready to help with your orders and questions.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     CALL TO ACTION
     ============================================ -->
<section class="section">
    <div class="container">
        <div style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); border-radius: 16px; padding: 4rem; text-align: center; color: white;">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem; color: white;">Start Your Shopping Journey</h2>
            <p style="font-size: 1.25rem; margin-bottom: 2rem; color: var(--beige);">
                Discover unique handcrafted products that bring warmth and character to your home
            </p>
            <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-lg" style="background: white; color: var(--primary-color);">
                <i class="fas fa-shopping-bag"></i> Browse All Products
            </a>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
