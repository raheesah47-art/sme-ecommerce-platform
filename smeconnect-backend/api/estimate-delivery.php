<?php
// GET /api/estimate-delivery.php?district_id=1&subtotal=2170
header('Content-Type: application/json');
require __DIR__ . '/../includes/db.php';

$districtId = isset($_GET['district_id']) ? (int) $_GET['district_id'] : 0;
$subtotal   = isset($_GET['subtotal']) ? (float) $_GET['subtotal'] : 0;

if (!$districtId) {
    http_response_code(400);
    echo json_encode(['error' => 'district_id is required']);
    exit;
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT id, name, base_fee, free_delivery_threshold FROM districts WHERE id = ?');
$stmt->execute([$districtId]);
$district = $stmt->fetch();

if (!$district) {
    http_response_code(404);
    echo json_encode(['error' => 'District not found']);
    exit;
}

$isFree = $subtotal >= (float) $district['free_delivery_threshold'];
$fee    = $isFree ? 0.0 : (float) $district['base_fee'];

echo json_encode([
    'district_id' => (int) $district['id'],
    'district'    => $district['name'],
    'fee'         => round($fee, 2),
    'free'        => $isFree,
    'threshold'   => (float) $district['free_delivery_threshold'],
]);