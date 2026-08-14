<?php
/**
 * includes/cart.php
 * Session-based cart helpers. No login required to add to cart —
 * matches an anonymous browsing/checkout flow. Requires db.php.
 */

/**
 * Returns the current cart token, generating one into the session if needed.
 * This is what ties cart_items rows to "this browser's cart" without a login.
 */
function get_cart_token(): string
{
    if (empty($_SESSION['cart_token'])) {
        $_SESSION['cart_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['cart_token'];
}

/**
 * Adds a product to the cart, or increments quantity if it's already in there.
 * Returns ['success' => bool, 'error' => string|null]
 */
function add_to_cart(PDO $pdo, int $productId, int $quantity = 1): array
{
    if ($quantity < 1) {
        return ['success' => false, 'error' => 'Quantity must be at least 1.'];
    }

    $stmt = $pdo->prepare('SELECT id, stock_qty FROM products WHERE id = ? AND status = "active"');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        return ['success' => false, 'error' => 'Product not found.'];
    }

    $cartToken = get_cart_token();

    $stmt = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE cart_token = ? AND product_id = ?');
    $stmt->execute([$cartToken, $productId]);
    $existing = $stmt->fetch();

    $newQty = ($existing ? (int) $existing['quantity'] : 0) + $quantity;

    if ($newQty > (int) $product['stock_qty']) {
        return ['success' => false, 'error' => 'Only ' . $product['stock_qty'] . ' left in stock.'];
    }

    if ($existing) {
        $stmt = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
        $stmt->execute([$newQty, $existing['id']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO cart_items (cart_token, product_id, quantity) VALUES (?, ?, ?)');
        $stmt->execute([$cartToken, $productId, $quantity]);
    }

    return ['success' => true, 'error' => null];
}

/**
 * Sets a cart item to an exact quantity. Quantity 0 or less removes it.
 * $cartItemId must belong to the current cart_token (checked here).
 */
function update_cart_item(PDO $pdo, int $cartItemId, int $quantity): array
{
    $cartToken = get_cart_token();

    $stmt = $pdo->prepare('SELECT ci.id, ci.product_id, p.stock_qty FROM cart_items ci
                            JOIN products p ON p.id = ci.product_id
                            WHERE ci.id = ? AND ci.cart_token = ?');
    $stmt->execute([$cartItemId, $cartToken]);
    $item = $stmt->fetch();

    if (!$item) {
        return ['success' => false, 'error' => 'Cart item not found.'];
    }

    if ($quantity <= 0) {
        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE id = ?');
        $stmt->execute([$cartItemId]);
        return ['success' => true, 'error' => null];
    }

    if ($quantity > (int) $item['stock_qty']) {
        return ['success' => false, 'error' => 'Only ' . $item['stock_qty'] . ' left in stock.'];
    }

    $stmt = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
    $stmt->execute([$quantity, $cartItemId]);
    return ['success' => true, 'error' => null];
}

/**
 * Removes a cart item outright. Scoped to the current cart_token.
 */
function remove_cart_item(PDO $pdo, int $cartItemId): array
{
    $cartToken = get_cart_token();
    $stmt = $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND cart_token = ?');
    $stmt->execute([$cartItemId, $cartToken]);
    return ['success' => true, 'error' => null];
}

/**
 * Returns the full cart: line items + subtotal + total savings.
 * This is the single source of truth the front end renders from.
 */
function get_cart(PDO $pdo): array
{
    $cartToken = get_cart_token();

    $stmt = $pdo->prepare('SELECT ci.id AS cart_item_id, ci.quantity,
                                   p.id AS product_id, p.name, p.category, p.price, p.compare_at_price,
                                   s.business_name AS seller_name
                            FROM cart_items ci
                            JOIN products p ON p.id = ci.product_id
                            JOIN sellers s ON s.id = p.seller_id
                            WHERE ci.cart_token = ?
                            ORDER BY ci.id ASC');
    $stmt->execute([$cartToken]);
    $rows = $stmt->fetchAll();

    $subtotal = 0.0;
    $savings  = 0.0;
    $items    = [];

    foreach ($rows as $row) {
        $lineTotal = (float) $row['price'] * (int) $row['quantity'];
        $subtotal += $lineTotal;
        if ($row['compare_at_price']) {
            $savings += ((float) $row['compare_at_price'] - (float) $row['price']) * (int) $row['quantity'];
        }
        $items[] = [
            'cart_item_id' => (int) $row['cart_item_id'],
            'product_id'   => (int) $row['product_id'],
            'name'         => $row['name'],
            'category'     => $row['category'],
            'seller_name'  => $row['seller_name'],
            'quantity'     => (int) $row['quantity'],
            'price'        => (float) $row['price'],
            'line_total'   => round($lineTotal, 2),
        ];
    }

    return [
        'items'    => $items,
        'subtotal' => round($subtotal, 2),
        'savings'  => round($savings, 2),
        'count'    => count($items),
    ];
}