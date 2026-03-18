<?php
/**
 * ============================================
 * SHOP PAGE - Product Listing
 * ============================================
 */

require_once 'config/config.php';
require_once 'includes/product-card.php';

// Page metadata
$page_title = 'Shop';
$meta_description = 'Browse our collection of handcrafted products and vintage treasures.';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Filters
$category_slug = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 10000;
$filter = $_GET['filter'] ?? ''; // featured, new, bestseller

// Build query
$where_conditions = ['p.is_active = 1'];
$params = [];

if ($category_slug) {
    $where_conditions[] = 'c.slug = ?';
    $params[] = $category_slug;
}

if ($search) {
    $where_conditions[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($min_price > 0) {
    $where_conditions[] = 'COALESCE(p.discount_price, p.price) >= ?';
    $params[] = $min_price;
}

if ($max_price < 10000) {
    $where_conditions[] = 'COALESCE(p.discount_price, p.price) <= ?';
    $params[] = $max_price;
}

if ($filter === 'featured') {
    $where_conditions[] = 'p.is_featured = 1';
} elseif ($filter === 'new') {
    $where_conditions[] = 'p.is_new = 1';
} elseif ($filter === 'bestseller') {
    $where_conditions[] = 'p.is_bestseller = 1';
}

$where_clause = implode(' AND ', $where_conditions);

// Sorting
$order_by = match($sort) {
    'price_low' => 'COALESCE(p.discount_price, p.price) ASC',
    'price_high' => 'COALESCE(p.discount_price, p.price) DESC',
    'name' => 'p.name ASC',
    'popular' => 'p.sales_count DESC',
    default => 'p.created_at DESC'
};

// Get total count
$count_sql = "
    SELECT COUNT(DISTINCT p.product_id) as total
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE $where_clause
";
$total_result = db_fetch($count_sql, $params);
$total_products = $total_result['total'] ?? 0;
$total_pages = ceil($total_products / $per_page);

// Get products
$sql = "
    SELECT p.*, c.name as category_name, pi.image_path,
           AVG(r.rating) as avg_rating, COUNT(DISTINCT r.review_id) as review_count
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    LEFT JOIN reviews r ON p.product_id = r.product_id AND r.is_approved = 1
    WHERE $where_clause
    GROUP BY p.product_id
    ORDER BY $order_by
    LIMIT $per_page OFFSET $offset
";

$products = db_fetch_all($sql, $params);

// Get categories for filter
$categories = db_fetch_all("
    SELECT category_id, name, slug
    FROM categories
    WHERE is_active = 1
    ORDER BY name ASC
");

// Include header
include 'includes/header.php';
?>

<!-- ============================================
     SHOP HEADER
     ============================================ -->
<section style="background: var(--bg-tertiary); padding: 2rem 0; margin-bottom: 2rem;">
    <div class="container">
        <h1 style="margin-bottom: 0.5rem;">Shop</h1>
        <p style="color: var(--text-secondary);">
            <?php if ($category_slug): ?>
                <?php 
                $current_category = array_filter($categories, fn($c) => $c['slug'] === $category_slug);
                $current_category = reset($current_category);
                echo 'Category: ' . htmlspecialchars($current_category['name'] ?? 'All Products');
                ?>
            <?php elseif ($search): ?>
                Search results for: "<?php echo htmlspecialchars($search); ?>"
            <?php else: ?>
                Browse our collection of handcrafted products
            <?php endif; ?>
        </p>
    </div>
</section>

<!-- ============================================
     SHOP CONTENT
     ============================================ -->
<section class="section-sm">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-3">
                <div class="filters-sidebar" style="background: var(--bg-primary); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-sm);">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Filters</h3>
                    
                    <!-- Categories -->
                    <div style="margin-bottom: 2rem;">
                        <h4 style="font-size: 1rem; margin-bottom: 1rem; color: var(--text-primary);">Categories</h4>
                        <ul style="list-style: none;">
                            <li style="margin-bottom: 0.5rem;">
                                <a href="<?php echo BASE_URL; ?>shop.php" 
                                   style="color: <?php echo !$category_slug ? 'var(--primary-color)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo !$category_slug ? '600' : '400'; ?>;">
                                    All Products
                                </a>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                                <li style="margin-bottom: 0.5rem;">
                                    <a href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $cat['slug']; ?>" 
                                       style="color: <?php echo $category_slug === $cat['slug'] ? 'var(--primary-color)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo $category_slug === $cat['slug'] ? '600' : '400'; ?>;">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <!-- Price Range -->
                    <div style="margin-bottom: 2rem;">
                        <h4 style="font-size: 1rem; margin-bottom: 1rem; color: var(--text-primary);">Price Range</h4>
                        <form method="GET" action="shop.php">
                            <?php if ($category_slug): ?>
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_slug); ?>">
                            <?php endif; ?>
                            <?php if ($search): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                            
                            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                                <input 
                                    type="number" 
                                    name="min_price" 
                                    placeholder="Min" 
                                    value="<?php echo $min_price > 0 ? $min_price : ''; ?>"
                                    class="form-control"
                                    style="padding: 0.5rem; font-size: 0.875rem;"
                                >
                                <input 
                                    type="number" 
                                    name="max_price" 
                                    placeholder="Max" 
                                    value="<?php echo $max_price < 10000 ? $max_price : ''; ?>"
                                    class="form-control"
                                    style="padding: 0.5rem; font-size: 0.875rem;"
                                >
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm btn-block">Apply</button>
                        </form>
                    </div>
                    
                    <!-- Quick Filters -->
                    <div>
                        <h4 style="font-size: 1rem; margin-bottom: 1rem; color: var(--text-primary);">Quick Filters</h4>
                        <a href="<?php echo BASE_URL; ?>shop.php?filter=featured" class="btn btn-outline btn-sm btn-block" style="margin-bottom: 0.5rem;">
                            <i class="fas fa-star"></i> Featured
                        </a>
                        <a href="<?php echo BASE_URL; ?>shop.php?filter=new" class="btn btn-outline btn-sm btn-block" style="margin-bottom: 0.5rem;">
                            <i class="fas fa-sparkles"></i> New Arrivals
                        </a>
                        <a href="<?php echo BASE_URL; ?>shop.php?filter=bestseller" class="btn btn-outline btn-sm btn-block">
                            <i class="fas fa-fire"></i> Best Sellers
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-9">
                <!-- Toolbar -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding: 1rem; background: var(--bg-primary); border-radius: 8px;">
                    <div>
                        <strong><?php echo $total_products; ?></strong> products found
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <label for="sortSelect" style="margin: 0; font-weight: 500;">Sort by:</label>
                        <select 
                            id="sortSelect" 
                            class="form-control" 
                            style="width: auto; padding: 0.5rem; font-size: 0.875rem;"
                            onchange="window.location.href='?<?php echo http_build_query(array_merge($_GET, ['sort' => ''])); ?>&sort=' + this.value"
                        >
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name: A-Z</option>
                            <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                        </select>
                    </div>
                </div>
                
                <!-- Products -->
                <?php if (!empty($products)): ?>
                    <?php render_product_grid($products, 3); ?>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-top: 3rem;">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                                   class="btn btn-outline btn-sm">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                                   class="btn btn-outline btn-sm">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center" style="padding: 4rem 0;">
                        <i class="fas fa-search" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                        <h3>No products found</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 2rem;">Try adjusting your filters or search terms</p>
                        <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-primary">View All Products</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
