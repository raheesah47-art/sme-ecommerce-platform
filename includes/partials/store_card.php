<?php
/**
 * includes/partials/store_card.php
 * Expects $store: id, business_name, district, trust_score,
 * verified_by_admin, product_count, logo_path (nullable)
 */
?>
<a class="store-card" href="<?= e(SITE_URL) ?>/pages/store.php?id=<?= (int) $store['id'] ?>">
    <div class="store-card-logo">
        <?php if (!empty($store['logo_path'])): ?>
            <img src="<?= e(UPLOAD_URL_LOGOS . $store['logo_path']) ?>" alt="<?= e($store['business_name']) ?>">
        <?php else: ?>
            <div class="no-image-placeholder"><?= e(mb_substr($store['business_name'], 0, 1)) ?></div>
        <?php endif; ?>
    </div>
    <div class="store-card-body">
        <h3><?= e($store['business_name']) ?>
            <?php if ($store['verified_by_admin']): ?><span class="badge badge-verified">✓</span><?php endif; ?>
        </h3>
        <p class="store-card-district">📍 <?= e($store['district'] ?? 'Mauritius') ?></p>
        <p class="trust-badge" title="Trust Score">★ <?= (int) $store['trust_score'] ?>/100</p>
        <p class="store-card-count"><?= (int) $store['product_count'] ?> product<?= (int) $store['product_count'] === 1 ? '' : 's' ?></p>
    </div>
</a>