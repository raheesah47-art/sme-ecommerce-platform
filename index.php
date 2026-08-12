<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';

$categories = $pdo->query('SELECT id, name, slug FROM categories ORDER BY name')->fetchAll();

$featuredStmt = $pdo->query(
    "SELECT p.id, p.name, p.slug, p.price, p.stock_quantity,
            s.business_name AS sme_business_name, s.trust_score AS sme_trust_score,
            (SELECT image_path FROM product_images pi
             WHERE pi.product_id = p.id
             ORDER BY pi.is_primary DESC, pi.sort_order ASC LIMIT 1) AS primary_image
     FROM products p
     JOIN smes s ON s.id = p.sme_id AND s.status = 'active'
     WHERE p.status = 'active'
     ORDER BY p.created_at DESC
     LIMIT 8"
);
$featuredProducts = $featuredStmt->fetchAll();

$topStoresStmt = $pdo->query(
    "SELECT id, business_name, district, trust_score, verified_by_admin, logo_path,
            (SELECT COUNT(*) FROM products p WHERE p.sme_id = smes.id AND p.status = 'active') AS product_count
     FROM smes
     WHERE status = 'active'
     ORDER BY trust_score DESC
     LIMIT 4"
);
$topStores = $topStoresStmt->fetchAll();

$pageTitle = 'Home';
include __DIR__ . '/includes/partials/header.php';
?>

<section class="hero">
    <h1>Discover Mauritian-made products</h1>
    <p>Shop directly from local SMEs — transparent trust scores, no middlemen.</p>
    <a href="pages/products.php" class="btn-primary hero-cta">Browse All Products</a>
</section>

<section class="category-strip">
    <?php foreach ($categories as $cat): ?>
        <a class="category-pill" href="pages/products.php?category=<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a>
    <?php endforeach; ?>
</section>

<section>
    <h2>New Arrivals</h2>
    <div class="product-grid">
        <?php if (empty($featuredProducts)): ?>
            <p class="empty-state">No products yet.</p>
        <?php else: ?>
            <?php foreach ($featuredProducts as $product): ?>
                <?php include __DIR__ . '/includes/partials/product_card.php'; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<section>
    <h2>Top Rated Sellers</h2>
    <div class="store-grid">
        <?php foreach ($topStores as $store): ?>
            <?php include __DIR__ . '/includes/partials/store_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/partials/footer.php'; ?>