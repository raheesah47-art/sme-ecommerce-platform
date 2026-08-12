<?php
/**
 * includes/partials/header.php
 * Shared header + nav bar. Assumes config.php, db.php, functions.php,
 * security.php, auth.php are already included by the calling page,
 * and that $pageTitle is optionally set before including this file.
 */
$user = current_user();
$pageTitle = $pageTitle ?? SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> — <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= e(SITE_URL) ?>/css/style.css">
<script>window.SITE_URL = <?= json_encode(SITE_URL) ?>;</script>
</head>
<body>
<header class="site-header">
    <div class="logo"><a href="<?= e(SITE_URL) ?>/index.php">SME Connect <span class="logo-accent">Mauritius</span></a></div>
    <nav class="main-nav">
        <a href="<?= e(SITE_URL) ?>/pages/products.php">Products</a>
        <a href="<?= e(SITE_URL) ?>/pages/stores.php">Stores</a>
        <?php if ($user): ?>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="<?= e(SITE_URL) ?>/admin/index.php">Admin Dashboard</a>
            <?php elseif ($user['role'] === 'sme'): ?>
                <a href="<?= e(SITE_URL) ?>/sme/dashboard.php">My Dashboard</a>
            <?php endif; ?>
            <span class="nav-user">Hi, <?= e($user['full_name']) ?></span>
            <a href="<?= e(SITE_URL) ?>/logout.php">Log out</a>
        <?php else: ?>
            <a href="<?= e(SITE_URL) ?>/login.php">Log in</a>
            <a href="<?= e(SITE_URL) ?>/register.php" class="nav-cta">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="page-content">
<?php render_flash(); ?>