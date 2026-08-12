<?php
/**
 * includes/auth.php
 * Registration, login, logout, and role-based access control.
 * Requires config.php, db.php, functions.php to already be included.
 */

/**
 * Returns the logged-in user's array (from $_SESSION), or null if guest.
 * Only session data is used here (no DB hit) for speed on every page load.
 */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function current_role(): ?string
{
    return $_SESSION['user']['role'] ?? null;
}

/**
 * Blocks access unless the user is logged in. Redirects to login.php.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

/**
 * Blocks access unless the user is logged in AND holds one of the given roles.
 * Example: require_role(['admin']); or require_role(['sme', 'admin']);
 */
function require_role(array $allowedRoles): void
{
    require_login();
    if (!in_array(current_role(), $allowedRoles, true)) {
        http_response_code(403);
        die('You do not have permission to access this page.');
    }
}

/**
 * Registers a new user (customer or sme). Admin accounts are never
 * created through this public form — only seeded directly in the DB.
 *
 * Returns ['success' => bool, 'error' => string|null]
 */
function register_user(PDO $pdo, array $data): array
{
    $fullName = clean_input($data['full_name'] ?? '');
    $email    = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';
    $role     = ($data['role'] ?? 'customer') === 'sme' ? 'sme' : 'customer';
    $phone    = clean_input($data['phone'] ?? '');
    $address  = clean_input($data['address'] ?? '');
    $district = clean_input($data['district'] ?? '');
    $businessName = clean_input($data['business_name'] ?? '');

    if (!$fullName || !$email || !$password) {
        return ['success' => false, 'error' => 'Full name, email and password are required.'];
    }
    if (!is_valid_email($email)) {
        return ['success' => false, 'error' => 'Please enter a valid email address.'];
    }
    if (!is_valid_password($password)) {
        return ['success' => false, 'error' => 'Password must be at least 8 characters and include a letter and a number.'];
    }
    if ($phone && !is_valid_phone($phone)) {
        return ['success' => false, 'error' => 'Please enter a valid Mauritian phone number.'];
    }
    if ($role === 'sme' && !$businessName) {
        return ['success' => false, 'error' => 'Business name is required for SME accounts.'];
    }

    // Check for duplicate email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'An account with this email already exists.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO users (full_name, email, password_hash, role, phone, address, district)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$fullName, $email, $hash, $role, $phone, $address, $district]);
        $userId = (int) $pdo->lastInsertId();

        if ($role === 'sme') {
            $stmt = $pdo->prepare(
                'INSERT INTO smes (user_id, business_name, district, business_phone, verified_by_admin, trust_score)
                 VALUES (?, ?, ?, ?, 0, 0)'
            );
            $stmt->execute([$userId, $businessName, $district, $phone]);
        }

        $pdo->commit();
        return ['success' => true, 'error' => null];
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = (defined('DEV_MODE') && DEV_MODE) ? $e->getMessage() : 'Registration failed. Please try again.';
        return ['success' => false, 'error' => $msg];
    }
}

/**
 * Attempts to log a user in. On success, regenerates the session ID
 * (prevents session fixation) and stores a minimal user array in $_SESSION.
 *
 * Returns ['success' => bool, 'error' => string|null]
 */
function login_user(PDO $pdo, string $email, string $password): array
{
    $email = strtolower(trim($email));

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Incorrect email or password.'];
    }

    if ($user['status'] === 'suspended') {
        return ['success' => false, 'error' => 'This account has been suspended. Contact support.'];
    }

    // Regenerate session ID on privilege change (login) to prevent session fixation.
    session_regenerate_id(true);

    $sessionUser = [
        'id'        => (int) $user['id'],
        'full_name' => $user['full_name'],
        'email'     => $user['email'],
        'role'      => $user['role'],
    ];

    // For SME accounts, also cache their sme_id so dashboard pages don't
    // need an extra lookup on every request.
    if ($user['role'] === 'sme') {
        $stmt = $pdo->prepare('SELECT id FROM smes WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        $sme = $stmt->fetch();
        $sessionUser['sme_id'] = $sme ? (int) $sme['id'] : null;
    }

    $_SESSION['user'] = $sessionUser;

    return ['success' => true, 'error' => null];
}

/**
 * Logs the current user out: clears session data and destroys the session.
 */
function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie('PHPSESSID', '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}