<?php
// GET /api/trust-score.php?seller_id=1
header('Content-Type: application/json');
require __DIR__ . '/../includes/db.php';

$sellerId = isset($_GET['seller_id']) ? (int) $_GET['seller_id'] : 0;

if (!$sellerId) {
    http_response_code(400);
    echo json_encode(['error' => 'seller_id is required']);
    exit;
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT * FROM sellers WHERE id = ?');
$stmt->execute([$sellerId]);
$seller = $stmt->fetch();

if (!$seller) {
    http_response_code(404);
    echo json_encode(['error' => 'Seller not found']);
    exit;
}

// Trust score formula — 4 weighted factors, capped 0-100.
// Documented here so it's easy to defend in a dissertation write-up.
$ratingScore   = (min((float) $seller['avg_rating'], 5) / 5) * 40;      // up to 40 pts
$verifiedScore = ((int) $seller['id_verified'] === 1) ? 20 : 0;         // 20 pts if ID-verified
$responseScore = (min((float) $seller['response_rate'], 100) / 100) * 20; // up to 20 pts
$disputeScore  = max(0, 20 - ((int) $seller['disputes_count'] * 5));    // up to 20 pts, -5 per dispute

$total = (int) round($ratingScore + $verifiedScore + $responseScore + $disputeScore);
$total = max(0, min(100, $total));

echo json_encode([
    'seller_id'   => (int) $seller['id'],
    'seller'      => $seller['business_name'],
    'trust_score' => $total,
    'breakdown'   => [
        'rating'         => round($ratingScore, 1),
        'verified'       => $verifiedScore,
        'responsiveness' => round($responseScore, 1),
        'dispute_record' => $disputeScore,
    ],
]);