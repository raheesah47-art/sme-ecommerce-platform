<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$old = ['full_name' => '', 'email' => '', 'role' => 'customer', 'phone' => '', 'address' => '', 'district' => '', 'business_name' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_protect();

    $old = [
        'full_name'     => $_POST['full_name'] ?? '',
        'email'         => $_POST['email'] ?? '',
        'role'          => $_POST['role'] ?? 'customer',
        'phone'         => $_POST['phone'] ?? '',
        'address'       => $_POST['address'] ?? '',
        'district'      => $_POST['district'] ?? '',
        'business_name' => $_POST['business_name'] ?? '',
    ];

    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($password !== $passwordConfirm) {
        $errors[] = 'Passwords do not match.';
    } else {
        $result = register_user($pdo, array_merge($old, ['password' => $password]));
        if ($result['success']) {
            set_flash('success', 'Account created successfully. Please log in.');
            redirect('login.php');
        } else {
            $errors[] = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <h1><?= e(SITE_NAME) ?></h1>
        <h2>Create an account</h2>

        <?php if ($errors): ?>
            <div class="flash-message flash-error">
                <?php foreach ($errors as $err): ?>
                    <p><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate>
            <?= csrf_field() ?>

            <label for="role">I am registering as</label>
            <select name="role" id="role" onchange="document.getElementById('sme-fields').style.display = this.value === 'sme' ? 'block' : 'none';">
                <option value="customer" <?= $old['role'] === 'customer' ? 'selected' : '' ?>>Customer (I want to buy)</option>
                <option value="sme" <?= $old['role'] === 'sme' ? 'selected' : '' ?>>SME (I want to sell)</option>
            </select>

            <label for="full_name">Full name</label>
            <input type="text" name="full_name" id="full_name" value="<?= e($old['full_name']) ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= e($old['email']) ?>" required>

            <label for="phone">Phone</label>
            <input type="text" name="phone" id="phone" placeholder="e.g. 57123456" value="<?= e($old['phone']) ?>">

            <label for="district">District</label>
            <select name="district" id="district">
                <option value="">-- Select district --</option>
                <?php foreach (MAURITIUS_DISTRICTS as $d): ?>
                    <option value="<?= e($d) ?>" <?= $old['district'] === $d ? 'selected' : '' ?>><?= e($d) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="address">Address</label>
            <input type="text" name="address" id="address" value="<?= e($old['address']) ?>">

            <div id="sme-fields" style="display: <?= $old['role'] === 'sme' ? 'block' : 'none' ?>;">
                <label for="business_name">Business name</label>
                <input type="text" name="business_name" id="business_name" value="<?= e($old['business_name']) ?>">
            </div>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
            <small>At least 8 characters, including a letter and a number.</small>

            <label for="password_confirm">Confirm password</label>
            <input type="password" name="password_confirm" id="password_confirm" required>

            <button type="submit" class="btn-primary">Create account</button>
        </form>

        <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>
    </div>
</div>
</body>
</html>