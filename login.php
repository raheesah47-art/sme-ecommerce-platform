<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = null;
$emailOld = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();

    $emailOld = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = login_user($pdo, $emailOld, $password);

    if ($result['success']) {
        $role = current_role();
        set_flash('success', 'Welcome back!');
        if ($role === 'admin') {
            redirect('admin/index.php');
        } elseif ($role === 'sme') {
            redirect('sme/dashboard.php');
        } else {
            redirect('index.php');
        }
    } else {
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log In — <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <h1><?= e(SITE_NAME) ?></h1>
        <h2>Log in</h2>

        <?php render_flash(); ?>

        <?php if ($error): ?>
            <div class="flash-message flash-error"><p><?= e($error) ?></p></div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <?= csrf_field() ?>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= e($emailOld) ?>" required autofocus>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" class="btn-primary">Log in</button>
        </form>

        <p class="auth-switch">Don't have an account? <a href="register.php">Register</a></p>

        <details class="demo-accounts">
            <summary>Demo accounts (for testing)</summary>
            <ul>
                <li>Admin: admin@smeconnect.mu / Admin@123</li>
                <li>SME: craftsmu@smeconnect.mu / SmeOwner@123</li>
                <li>Customer: customer1@example.com / Customer@123</li>
            </ul>
        </details>
    </div>
</div>
</body>
</html>