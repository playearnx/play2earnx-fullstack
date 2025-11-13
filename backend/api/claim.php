<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/rate_limiter.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){ http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit; }
$body = json_decode(file_get_contents('php://input'), true);
$token = $body['token'] ?? '';
$score = intval($body['score'] ?? 0);
$address = $body['address'] ?? '';

if(!$token || !$score || !$address){ http_response_code(400); echo json_encode(['error'=>'token, score, address required']); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT user_id FROM sessions WHERE token = ? LIMIT 1');
$stmt->execute([$token]);
$sess = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$sess){ http_response_code(401); echo json_encode(['error'=>'Invalid session']); exit; }
$user_id = $sess['user_id'];

// rate limit per user per action
$key = 'claim|'.$user_id;
if(!rate_limit_check($key, CLAIM_RATE_LIMIT_MAX, CLAIM_RATE_LIMIT_WINDOW)){
  http_response_code(429); echo json_encode(['error'=>'Too many claims']); exit;
}

// basic anti-cheat: limit per-claim score and daily cap
if($score < 1 || $score > MAX_SCORE_PER_CLAIM){ http_response_code(400); echo json_encode(['error'=>'Invalid score']); exit; }
$stmt = $pdo->prepare('SELECT SUM(amount) as total FROM tx_log WHERE user_id = ? AND DATE(created_at)=CURDATE() AND type="claim"');
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$today_total = floatval($row['total'] ?? 0);
if($today_total >= DAILY_CLAIM_CAP){ http_response_code(403); echo json_encode(['error'=>'Daily claim cap reached']); exit; }

// compute token amount from score (simple conversion)
$amount = floor($score / POINTS_PER_TOKEN);
if($amount <= 0){ http_response_code(400); echo json_encode(['error'=>'Not enough points']); exit; }

// insert claim record (pending)
$nonce = (string)(round(microtime(true)*1000) . rand(100,999));
$claim_hash = hash('sha256', $address . '|' . $amount . '|' . $nonce . '|' . CONTRACT_ADDRESS);

$stmt = $pdo->prepare('INSERT INTO user_claims (user_id, wallet_address, score, amount, claim_hash, nonce, claimed, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())');
$stmt->execute([$user_id, $address, $score, $amount, $claim_hash, $nonce]);

// If BACKEND_SIGNER_URL is set, request signature from signing server
if(!empty(BACKEND_SIGNER_URL)){
  $payload = ['address'=>$address, 'amount'=> (string) bcmul($amount, bcpow(10, TOKEN_DECIMALS)), 'nonce'=>$nonce];
  $ch = curl_init(rtrim(BACKEND_SIGNER_URL, '/') . '/request-claim');
  curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode($payload), CURLOPT_HTTPHEADER=>['Content-Type: application/json']]);
  $resp = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
  if($err){ echo json_encode(['error'=>'signer request failed','detail'=>$err]); exit; }
  $j = json_decode($resp, true);
  if(!$j || empty($j['signature'])){ echo json_encode(['error'=>'signer failed','detail'=>$resp]); exit; }
  // mark as claimed pending on-chain
  echo json_encode(['ok'=>true, 'signature'=>$j['signature'], 'nonce'=>$nonce, 'amount'=>bcmul($amount, bcpow(10, TOKEN_DECIMALS)), 'claim_hash'=>$claim_hash]);
  exit;
}

// otherwise, return pending response
echo json_encode(['ok'=>true, 'pending'=>true, 'claim_hash'=>$claim_hash, 'amount'=>$amount]);
