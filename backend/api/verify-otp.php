<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST'){ http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit; }
$data = json_decode(file_get_contents('php://input'), true);
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$otp = $data['otp'] ?? '';
if(!$email || !$otp){ http_response_code(400); echo json_encode(['error'=>'email and otp required']); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT * FROM otp_requests WHERE email = ? ORDER BY id DESC LIMIT 1');
$stmt->execute([$email]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$row){ http_response_code(400); echo json_encode(['error'=>'OTP not found']); exit; }
if(time() > strtotime($row['expires_at'])){ http_response_code(400); echo json_encode(['error'=>'OTP expired']); exit; }
if(!password_verify($otp, $row['otp_hash'])){
  $stmt = $pdo->prepare('UPDATE otp_requests SET attempts = attempts + 1 WHERE id = ?');
  $stmt->execute([$row['id']]);
  http_response_code(400); echo json_encode(['error'=>'Invalid OTP']); exit;
}
// success: create or fetch user and start session token
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$u){
  $stmt = $pdo->prepare('INSERT INTO users (email, role, balance, created_at) VALUES (?, ?, ?, NOW())');
  $stmt->execute([$email, 'player', 0]);
  $userid = $pdo->lastInsertId();
}else{ $userid = $u['id']; }

// create simple session token (store in sessions table)
$token = bin2hex(random_bytes(24));
$stmt = $pdo->prepare('INSERT INTO sessions (user_id, token, created_at) VALUES (?, ?, NOW())');
$stmt->execute([$userid, $token]);

echo json_encode(['ok'=>true, 'token'=>$token, 'user_id'=>$userid]);
