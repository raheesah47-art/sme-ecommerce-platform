<?php
/**
 * includes/partials/product_card.php
 * Expects $product to be an associative array with at least:
 * id, name, slug, price, stock_quantity, sme_business_name,
 * sme_trust_score, primary_image (nullable), category_name (nullable)
 */
?>
<a class="product-card" href="<?= e(SITE_URL) ?>/pages/product.php?id=<?= (int) $product['id'] ?>">
    <div class="product-card-image">
        <?php if (!empty($product['primary_image'])): ?>
            <img src="<?= e(UPLOAD_URL_PRODUCTS . $product['primary_image']) ?>" alt="<?= e($product['name']) ?>">
        <?php else: ?>
            <div class="no-image-placeholder"><?= e(mb_substr($product['name'], 0, 1)) ?></div>
        <?php endif; ?>
        <?php if ((int) $product['stock_quantity'] <= 0): ?>
            <span class="badge badge-out-of-stock">Out of stock</span>
        <?php endif; ?>
    </div>
    <div class="product-card-body">
        <h3><?= e($product['name']) ?></h3>
        <p class="product-card-seller">
            <?= e($product['sme_business_name']) ?>
            <span class="trust-badge" title="Trust Score">★ <?= (int) $product['sme_trust_score'] ?></span>
        </p>
        <p class="product-card-price">Rs <?= number_format((float) $product['price'], 2) ?></p>
    </div>
</a>