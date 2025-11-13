<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST'){ http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit; }
$body = json_decode(file_get_contents('php://input'), true);
$token = $body['token'] ?? '';
$tokens = intval($body['tokens'] ?? 0);
if(!$token || $tokens<=0){ http_response_code(400); echo json_encode(['error'=>'token and tokens required']); exit; }
$pdo = getPDO();
$stmt = $pdo->prepare('SELECT user_id, balance FROM sessions s JOIN users u ON s.user_id=u.id WHERE token = ? LIMIT 1');
$stmt->execute([$token]); $s = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$s){ http_response_code(401); echo json_encode(['error'=>'Invalid session']); exit; }
$user_id = $s['user_id']; $balance = floatval($s['balance']);
if($balance < $tokens){ http_response_code(400); echo json_encode(['error'=>'Insufficient balance']); exit; }
$fee = $tokens * MARKET_FEE_RATE;
$net = $tokens - $fee;
// record tx
$stmt = $pdo->prepare('INSERT INTO tx_log (user_id, type, amount, fee, created_at) VALUES (?, "sell", ?, ?, NOW())');
$stmt->execute([$user_id, $tokens, $fee]);
// update balance
$stmt = $pdo->prepare('UPDATE users SET balance = balance - ? WHERE id = ?');
$stmt->execute([$tokens, $user_id]);
echo json_encode(['ok'=>true, 'tokens_sold'=>$tokens, 'fee'=>$fee, 'net'=>$net]);
