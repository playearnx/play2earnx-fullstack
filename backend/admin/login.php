<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start();
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
  $otp = $_POST['otp'] ?? '';
  if($email && $otp){
    // call local API verify-otp.php
    $payload = json_encode(['email'=>$email,'otp'=>$otp]);
    $ch = curl_init('http://localhost/api/verify-otp.php');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>$payload, CURLOPT_HTTPHEADER=>['Content-Type: application/json']]);
    $resp = curl_exec($ch); $j = json_decode($resp,true);
    if(isset($j['token'])){
      // set admin session if user's role is admin in DB
      $pdo = getPDO();
      $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
      $stmt->execute([$j['user_id']]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if($row && $row['role']==='admin'){
        $_SESSION['admin_user'] = $email;
        $_SESSION['admin_token'] = $j['token'];
        header('Location: index.php'); exit;
      }else{
        $err='Not an admin account';
      }
    }else{
      $err = $j['error'] ?? 'OTP verify failed';
    }
  }else $err='Email & OTP required';
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="p-4">
<div class="container col-md-4">
  <h3>Admin Login (OTP)</h3>
  <?php if($err): ?><div class="alert alert-danger"><?=htmlspecialchars($err)?></div><?php endif; ?>
  <form method="post">
    <div class="mb-2"><label>Email</label><input name="email" class="form-control" required></div>
    <div class="mb-2"><label>OTP</label><input name="otp" class="form-control" required></div>
    <button class="btn btn-primary">Login</button>
    <a href="../api/request-otp.php" class="btn btn-link">Request OTP (use API)</a>
  </form>
</div></body></html>
