<?php
/**
 * includes/functions.php
 * General-purpose helper functions used across the site.
 */

/**
 * Redirects to a given path within the site and stops execution.
 */
function redirect(string $path): void
{
    header('Location: ' . SITE_URL . '/' . ltrim($path, '/'));
    exit;
}

/**
 * Stores a one-time "flash" message in the session to show after a redirect.
 * $type is 'success', 'error', or 'info' — used for CSS styling.
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Retrieves and clears the flash message, if any.
 * Returns null if there is nothing to show.
 */
function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Renders the flash message as HTML, if one is queued.
 * Call this near the top of the visible page body.
 */
function render_flash(): void
{
    $flash = get_flash();
    if (!$flash) {
        return;
    }
    $class = $flash['type'] === 'error' ? 'flash-error'
        : ($flash['type'] === 'success' ? 'flash-success' : 'flash-info');
    echo '<div class="flash-message ' . $class . '">' . e($flash['message']) . '</div>';
}

/**
 * Trims a string and returns null if it ends up empty.
 * Useful for optional text fields (e.g. address line 2).
 */
function clean_input(?string $value): ?string
{
    if ($value === null) {
        return null;
    }
    $value = trim($value);
    return $value === '' ? null : $value;
}

/**
 * Basic, defensible email format validation.
 */
function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Password strength check: at least 8 chars, one letter, one number.
 * Kept simple and explainable for the dissertation write-up.
 */
function is_valid_password(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Za-z]/', $password)
        && preg_match('/[0-9]/', $password);
}

/**
 * Mauritian phone number check: 7-8 digits, optional leading +230.
 */
function is_valid_phone(string $phone): bool
{
    return (bool) preg_match('/^(\+230)?[0-9]{7,8}$/', str_replace(' ', '', $phone));
}