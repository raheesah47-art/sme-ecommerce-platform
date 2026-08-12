<?php
// POST /api/chat.php  {message}
// Rule-based intent matching. The order-tracking intent does a real DB lookup,
// so replies aren't just canned text.
header('Content-Type: application/json');
require __DIR__ . '/../includes/db.php';

function respond(string $reply, array $quickReplies = []): void
{
    echo json_encode(['reply' => $reply, 'quick_replies' => $quickReplies]);
    exit;
}

$data    = json_decode(file_get_contents('php://input'), true) ?? [];
$message = strtolower(trim($data['message'] ?? ''));

$defaultQuickReplies = ['Track my order', 'Delivery fee', 'Talk to a seller'];

if ($message === '') {
    respond('Bonzour! I can help you track an order, check delivery fees, or find a seller. What do you need?', $defaultQuickReplies);
}

// Intent: order tracking — look for a code like SC-10493 anywhere in the message
if (preg_match('/\b(sc-\d+)\b/i', $message, $m)) {
    $code = strtoupper($m[1]);
    $pdo  = get_db();

    $stmt = $pdo->prepare('SELECT id, total FROM orders WHERE order_code = ?');
    $stmt->execute([$code]);
    $order = $stmt->fetch();

    if (!$order) {
        respond("I couldn't find an order with code {$code}. Double-check the code and try again.");
    }

    $stmt = $pdo->prepare('SELECT status FROM order_status_log WHERE order_id = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$order['id']]);
    $latest = $stmt->fetch();
    $status = $latest['status'] ?? 'placed';

    $friendly = [
        'placed'           => 'has been placed and is awaiting confirmation',
        'confirmed'        => 'has been confirmed by the seller',
        'out_for_delivery' => 'is out for delivery',
        'delivered'        => 'has been delivered',
    ];

    respond("Order {$code} {$friendly[$status]}. Total: Rs " . number_format((float) $order['total'], 2) . '.');
}

// Intent: user wants to track but didn't give a code
if (str_contains($message, 'track') || str_contains($message, 'order')) {
    respond("Sure — what's your order code? It looks like SC-10493.");
}

// Intent: delivery fee
if (str_contains($message, 'delivery') || str_contains($message, 'fee') || str_contains($message, 'shipping')) {
    respond('Delivery fees depend on your district — pick it on checkout and I\'ll show the exact cost. Orders over Rs 1,500 ship free island-wide.');
}

// Intent: talk to seller
if (str_contains($message, 'seller')) {
    respond("I can connect you with the seller directly from your order page — look for 'Message seller' under order details.");
}

// Intent: returns
if (str_contains($message, 'return') || str_contains($message, 'refund')) {
    respond('Returns are accepted within 7 days of delivery. Want me to start a return for a specific order?');
}

// Fallback
respond("I'm not sure I caught that — I can help with order tracking, delivery fees, or connecting you to a seller.", $defaultQuickReplies);