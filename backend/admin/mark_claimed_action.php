<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start();
if(!isset($_SESSION['admin_user'])){ header('Location: login.php'); exit; }
$claim_hash = $_POST['claim_hash'] ?? '';
if(!$claim_hash){ header('Location: index.php'); exit; }
$pdo = getPDO();
$stmt = $pdo->prepare('UPDATE user_claims SET claimed=1 WHERE claim_hash = ? LIMIT 1');
$stmt->execute([$claim_hash]);
// also log tx
$stmt = $pdo->prepare('INSERT INTO tx_log (user_id, type, amount, fee, created_at) SELECT user_id, "claim", amount, 0, NOW() FROM user_claims WHERE claim_hash = ? LIMIT 1');
$stmt->execute([$claim_hash]);
header('Location: index.php');
