<?php
// GET /api/order-status.php?order_code=SC-10493
// (Your current order-status.php is a copy-paste of trust-score.php —
// this replaces it with the actual order-tracking logic.)
header('Content-Type: application/json');
require __DIR__ . '/../includes/db.php';

$orderCode = isset($_GET['order_code']) ? strtoupper(trim($_GET['order_code'])) : '';

if (!$orderCode) {
    http_response_code(400);
    echo json_encode(['error' => 'order_code is required']);
    exit;
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT o.id, o.order_code, o.buyer_name, o.subtotal, o.delivery_fee, o.total, s.business_name AS seller_name
                        FROM orders o
                        JOIN sellers s ON s.id = o.seller_id
                        WHERE o.order_code = ?');
$stmt->execute([$orderCode]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
    exit;
}

$stmt = $pdo->prepare('SELECT status, note, created_at FROM order_status_log WHERE order_id = ? ORDER BY id ASC');
$stmt->execute([$order['id']]);
$history = $stmt->fetchAll();

$latestStatus = $history ? end($history)['status'] : 'placed';

echo json_encode([
    'order_code'    => $order['order_code'],
    'buyer_name'    => $order['buyer_name'],
    'seller_name'   => $order['seller_name'],
    'total'         => (float) $order['total'],
    'latest_status' => $latestStatus,
    'history'       => $history,
]);