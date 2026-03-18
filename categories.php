<?php
/**
 * ============================================
 * CATEGORIES PAGE
 * ============================================
 */

require_once 'config/config.php';

// Page metadata
$page_title = 'Categories';
$meta_description = 'Browse our product categories and discover unique handcrafted items.';

// Fetch all active categories with product counts
$categories = db_fetch_all("
    SELECT c.*, 
           COUNT(DISTINCT p.product_id) as product_count,
           MIN(COALESCE(p.discount_price, p.price)) as min_price,
           MAX(COALESCE(p.discount_price, p.price)) as max_price
    FROM categories c
    LEFT JOIN products p ON c.category_id = p.category_id AND p.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.category_id
    ORDER BY c.display_order ASC, c.name ASC
");

// Fetch subcategories
$subcategories = db_fetch_all("
    SELECT s.*, c.name as category_name, c.slug as category_slug,
           COUNT(DISTINCT p.product_id) as product_count
    FROM subcategories s
    LEFT JOIN categories c ON s.category_id = c.category_id
    LEFT JOIN products p ON s.subcategory_id = p.subcategory_id AND p.is_active = 1
    WHERE s.is_active = 1 AND c.is_active = 1
    GROUP BY s.subcategory_id
    ORDER BY s.display_order ASC
");

// Group subcategories by category
$grouped_subcategories = [];
foreach ($subcategories as $sub) {
    $grouped_subcategories[$sub['category_slug']][] = $sub;
}

// Include header
include 'includes/header.php';
?>

<!-- ============================================
     PAGE HEADER
     ============================================ -->
<section style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); padding: 4rem 0; color: white; text-align: center;">
    <div class="container">
        <h1 style="font-size: 3rem; margin-bottom: 1rem; color: white;">Browse Categories</h1>
        <p style="font-size: 1.25rem; color: var(--beige); max-width: 600px; margin: 0 auto;">
            Explore our curated collections of handcrafted products and vintage treasures
        </p>
    </div>
</section>

<!-- ============================================
     CATEGORIES GRID
     ============================================ -->
<section class="section">
    <div class="container">
        <?php if (!empty($categories)): ?>
            <div class="row">
                <?php foreach ($categories as $category): ?>
                    <div class="col-4" style="margin-bottom: 2rem;">
                        <div class="category-card" style="background: var(--bg-primary); border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-md); transition: all 0.3s ease; height: 100%;">
                            <!-- Category Image -->
                            <div style="position: relative; padding-top: 60%; overflow: hidden;">
                                <img 
                                    src="<?php echo $category['image'] ? BASE_URL . $category['image'] : 'https://images.unsplash.com/photo-1513519245088-0e12902e35ca?w=600&h=400&fit=crop'; ?>"
                                    alt="<?php echo htmlspecialchars($category['name']); ?>"
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;"
                                    onmouseover="this.style.transform='scale(1.1)'"
                                    onmouseout="this.style.transform='scale(1)'"
                                >
                                <!-- Overlay -->
                                <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);"></div>
                                
                                <!-- Product Count Badge -->
                                <div style="position: absolute; top: 1rem; right: 1rem; background: white; padding: 0.5rem 1rem; border-radius: 20px; box-shadow: var(--shadow-md);">
                                    <strong style="color: var(--primary-color);"><?php echo $category['product_count']; ?></strong>
                                    <span style="color: var(--text-secondary); font-size: 0.875rem;"> products</span>
                                </div>
                            </div>
                            
                            <!-- Category Info -->
                            <div style="padding: 2rem;">
                                <h2 style="font-size: 1.75rem; margin-bottom: 0.75rem; color: var(--text-primary);">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </h2>
                                
                                <?php if ($category['description']): ?>
                                    <p style="color: var(--text-secondary); margin-bottom: 1rem; line-height: 1.6;">
                                        <?php echo htmlspecialchars($category['description']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <!-- Price Range -->
                                <?php if ($category['product_count'] > 0): ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; color: var(--text-muted); font-size: 0.875rem;">
                                        <i class="fas fa-tag"></i>
                                        <span>
                                            <?php echo format_price($category['min_price']); ?> - 
                                            <?php echo format_price($category['max_price']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Subcategories -->
                                <?php if (isset($grouped_subcategories[$category['slug']]) && !empty($grouped_subcategories[$category['slug']])): ?>
                                    <div style="margin-bottom: 1.5rem;">
                                        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.5rem; font-weight: 600;">
                                            Subcategories:
                                        </p>
                                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                            <?php foreach (array_slice($grouped_subcategories[$category['slug']], 0, 4) as $sub): ?>
                                                <a href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $category['slug']; ?>&subcategory=<?php echo $sub['slug']; ?>" 
                                                   style="padding: 0.25rem 0.75rem; background: var(--bg-tertiary); border-radius: 20px; font-size: 0.75rem; color: var(--text-secondary); text-decoration: none; transition: all 0.3s;"
                                                   onmouseover="this.style.background='var(--primary-color)'; this.style.color='white';"
                                                   onmouseout="this.style.background='var(--bg-tertiary)'; this.style.color='var(--text-secondary)';">
                                                    <?php echo htmlspecialchars($sub['name']); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Browse Button -->
                                <a href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $category['slug']; ?>" 
                                   class="btn btn-primary btn-block">
                                    <i class="fas fa-arrow-right"></i> Browse <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center" style="padding: 4rem 0;">
                <i class="fas fa-folder-open" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                <h3>No categories available</h3>
                <p style="color: var(--text-secondary);">Check back soon for new categories!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================
     FEATURED CATEGORIES SECTION
     ============================================ -->
<?php
$featured_categories = array_filter($categories, function($cat) {
    return $cat['product_count'] > 0;
});
$featured_categories = array_slice($featured_categories, 0, 3);
?>

<?php if (!empty($featured_categories)): ?>
<section class="section" style="background: var(--bg-tertiary);">
    <div class="container">
        <div class="text-center" style="margin-bottom: 3rem;">
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Popular Categories</h2>
            <p style="color: var(--text-secondary); font-size: 1.125rem;">Most loved by our customers</p>
        </div>
        
        <div class="row">
            <?php foreach ($featured_categories as $cat): ?>
                <div class="col-4">
                    <a href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $cat['slug']; ?>" 
                       style="display: block; text-decoration: none; position: relative; border-radius: 12px; overflow: hidden; height: 300px;">
                        <img 
                            src="<?php echo $cat['image'] ? BASE_URL . $cat['image'] : 'https://images.unsplash.com/photo-1513519245088-0e12902e35ca?w=500&h=300&fit=crop'; ?>"
                            alt="<?php echo htmlspecialchars($cat['name']); ?>"
                            style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;"
                            onmouseover="this.style.transform='scale(1.1)'"
                            onmouseout="this.style.transform='scale(1)'"
                        >
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); display: flex; align-items: flex-end; padding: 2rem;">
                            <div>
                                <h3 style="color: white; font-size: 1.75rem; margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </h3>
                                <p style="color: var(--beige); font-size: 0.875rem;">
                                    <?php echo $cat['product_count']; ?> Products Available
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================
     CALL TO ACTION
     ============================================ -->
<section class="section">
    <div class="container">
        <div style="background: linear-gradient(135deg, var(--accent-color), var(--accent-dark)); border-radius: 16px; padding: 4rem; text-align: center; color: white;">
            <i class="fas fa-shopping-bag" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.9;"></i>
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem; color: white;">Can't Find What You're Looking For?</h2>
            <p style="font-size: 1.125rem; margin-bottom: 2rem; color: white; opacity: 0.9; max-width: 600px; margin-left: auto; margin-right: auto;">
                Browse all our products or use our search feature to find exactly what you need
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-lg" style="background: white; color: var(--primary-color);">
                    <i class="fas fa-th-large"></i> View All Products
                </a>
                <a href="<?php echo BASE_URL; ?>search.php" class="btn btn-outline btn-lg" style="border-color: white; color: white;">
                    <i class="fas fa-search"></i> Search Products
                </a>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
