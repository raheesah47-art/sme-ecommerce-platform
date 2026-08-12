<?php
/**
 * includes/security.php
 * CSRF protection and small security helpers.
 * Requires config.php (for session_start) to already be included.
 */

/**
 * Returns the current CSRF token, generating one if it doesn't exist yet.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Outputs a hidden input field carrying the CSRF token.
 * Use inside every <form> that changes data (POST forms).
 */
function csrf_field(): string
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Verifies a submitted CSRF token against the session token.
 * Call this at the top of every POST handler.
 */
function csrf_verify(?string $submittedToken): bool
{
    if (empty($_SESSION['csrf_token']) || empty($submittedToken)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $submittedToken);
}

/**
 * Dies with a 403 if the CSRF token in $_POST is missing/invalid.
 * Call as the very first line of any POST-handling script.
 */
function csrf_protect(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            die('Invalid or missing security token. Please refresh the page and try again.');
        }
    }
}

/**
 * Shorthand for htmlspecialchars(), used when echoing any
 * user-supplied or database value into HTML.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}