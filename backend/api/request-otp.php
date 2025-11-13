<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/rate_limiter.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'Method not allowed']); exit; }
$data = json_decode(file_get_contents('php://input'), true);
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
if(!$email){ http_response_code(400); echo json_encode(['error'=>'Invalid email']); exit; }

$key = $_SERVER['REMOTE_ADDR'].'|'.$email;
if(!rate_limit_check($key, OTP_RATE_LIMIT_MAX, OTP_RATE_LIMIT_WINDOW)){
  http_response_code(429); echo json_encode(['error'=>'Too many OTP requests']); exit;
}

$otp = random_int(100000,999999);
$hash = password_hash((string)$otp, PASSWORD_BCRYPT);
$expires = date('Y-m-d H:i:s', time() + OTP_EXPIRY_SECONDS);

$pdo = getPDO();
$stmt = $pdo->prepare('INSERT INTO otp_requests (email, otp_hash, expires_at, attempts, ip_address) VALUES (?, ?, ?, 0, ?)');
$stmt->execute([$email, $hash, $expires, $_SERVER['REMOTE_ADDR']]);

// send email
$sent = false;
if(!empty(SMTP_HOST)){
  try{
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress($email);
    $mail->Subject = 'Your OTP for Play2EarnX';
    $mail->Body = "Your OTP code is: $otp (valid for " . (OTP_EXPIRY_SECONDS/60) . " minutes).";
    $mail->send();
    $sent = true;
  }catch(Exception $e){
    // ignore - will allow debug show
  }
}

if(!$sent && DEBUG_SHOW_OTP){
  echo json_encode(['ok'=>true, 'debug_otp'=>$otp]);
  exit;
}

if($sent){
  echo json_encode(['ok'=>true]);
}else{
  http_response_code(500); echo json_encode(['error'=>'Failed to send OTP']); exit;
}
