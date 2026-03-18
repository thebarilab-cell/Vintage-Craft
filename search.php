<?php
/**
 * ============================================
 * SEARCH RESULTS PAGE
 * ============================================
 */

require_once 'config/config.php';
require_once 'includes/product-card.php';

$query = clean_input($_GET['search'] ?? '');
$page_title = $query ? "Search results for '$query'" : "Search Products";

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$products = [];
$total_products = 0;

if ($query) {
    // Exact name match or description match
    $search_term = "%$query%";
    
    // Get total count
    $count_res = db_fetch("
        SELECT COUNT(*) as total 
        FROM products 
        WHERE (name LIKE ? OR description LIKE ? OR sku LIKE ?) AND is_active = 1
    ", [$search_term, $search_term, $search_term]);
    
    $total_products = $count_res['total'] ?? 0;
    
    // Get results
    $products = db_fetch_all("
        SELECT p.*, pi.image_path as image
        FROM products p
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        WHERE (p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?) AND p.is_active = 1
        ORDER BY p.is_featured DESC, p.created_at DESC
        LIMIT $per_page OFFSET $offset
    ", [$search_term, $search_term, $search_term]);
}

include 'includes/header.php';
?>

<!-- ============================================
     SEARCH HEADER
     ============================================ -->
<section style="background: var(--bg-tertiary); padding: 5rem 0;">
    <div class="container text-center">
        <h1 style="margin-bottom: 2rem;">
            <?php if ($query): ?>
                Search results for: "<span style="color: var(--primary-color);"><?php echo htmlspecialchars($query); ?></span>"
            <?php else: ?>
                Search Our Collection
            <?php endif; ?>
        </h1>
        
        <form action="search.php" method="GET" style="max-width: 600px; margin: 0 auto; position: relative;">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                value="<?php echo htmlspecialchars($query); ?>" 
                placeholder="Search for pottery, jewelry, decor..." 
                style="height: 60px; padding-left: 2rem; padding-right: 120px; border-radius: 30px; border: 2px solid var(--border-color); font-size: 1.125rem;"
            >
            <button 
                type="submit" 
                class="btn btn-primary" 
                style="position: absolute; right: 8px; top: 8px; bottom: 8px; border-radius: 22px; padding: 0 1.5rem;"
            >
                <i class="fas fa-search"></i>
            </button>
        </form>
        
        <?php if ($query): ?>
            <p style="margin-top: 1.5rem; color: var(--text-secondary);">
                Showing <?php echo count($products); ?> of <?php echo $total_products; ?> results
            </p>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================
     RESULTS SECTION
     ============================================ -->
<section class="section">
    <div class="container">
        <?php if ($query): ?>
            <?php if (!empty($products)): ?>
                <?php render_product_grid($products, 4); ?>
                
                <!-- Pagination -->
                <?php if ($total_products > $per_page): ?>
                    <div class="pagination-container" style="text-align: center; margin-top: 4rem;">
                        <?php 
                        $total_pages = ceil($total_products / $per_page); 
                        for ($i = 1; $i <= $total_pages; $i++): 
                        ?>
                            <a href="search.php?search=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>" 
                               class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?>"
                               style="margin: 0 0.25rem; min-width: 45px;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div style="text-align: center; padding: 5rem 0;">
                    <div style="width: 120px; height: 120px; background: var(--bg-tertiary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; color: var(--text-muted);">
                        <i class="fas fa-search-minus" style="font-size: 3rem;"></i>
                    </div>
                    <h2 style="margin-bottom: 1rem;">No results found</h2>
                    <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto 2rem;">
                        We couldn't find anything matching your search. Please try different keywords or browse our categories.
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <a href="shop.php" class="btn btn-primary">Browse All Shop</a>
                        <a href="categories.php" class="btn btn-outline">Explore Categories</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Popular Searches / Empty Search State -->
            <div style="text-align: center; padding: 4rem 0;">
                <h3 style="margin-bottom: 2.5rem;">Popular Searches</h3>
                <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <?php 
                    $suggestions = ['Handmade Pottery', 'Vintage Decor', 'Artisan Jewelry', 'Eco-friendly', 'New Arrivals'];
                    foreach ($suggestions as $tag): 
                    ?>
                        <a href="search.php?search=<?php echo urlencode($tag); ?>" 
                           style="padding: 0.8rem 1.5rem; background: var(--bg-tertiary); border-radius: 30px; text-decoration: none; color: var(--text-primary); transition: all 0.3s; font-weight: 500;"
                           onmouseover="this.style.background='var(--primary-color)'; this.style.color='white'"
                           onmouseout="this.style.background='var(--bg-tertiary)'; this.style.color='var(--text-primary)'">
                            <?php echo $tag; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
