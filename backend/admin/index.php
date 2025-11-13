<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start();
if(!isset($_SESSION['admin_user'])){
  header('Location: login.php');
  exit;
}
$pdo = getPDO();
$claims = $pdo->query('SELECT uc.*, u.email FROM user_claims uc JOIN users u ON uc.user_id=u.id WHERE uc.claimed=0 ORDER BY uc.created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html><head><meta charset="utf-8"><title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="p-4">
<div class="container">
  <h3>Admin Dashboard</h3>
  <p>Logged in as: <?= htmlspecialchars($_SESSION['admin_user']) ?> <a href="logout.php" class="btn btn-sm btn-danger">Logout</a></p>
  <table class="table table-striped">
    <thead><tr><th>User Email</th><th>Wallet</th><th>Score</th><th>Amount</th><th>Nonce</th><th>Claim Hash</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach($claims as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['email']) ?></td>
        <td><?= htmlspecialchars($c['wallet_address']) ?></td>
        <td><?= htmlspecialchars($c['score']) ?></td>
        <td><?= htmlspecialchars($c['amount']) ?></td>
        <td><?= htmlspecialchars($c['nonce']) ?></td>
        <td style="word-break:break-all"><?= htmlspecialchars($c['claim_hash']) ?></td>
        <td>
          <form method="post" action="mark_claimed_action.php" style="display:inline">
            <input type="hidden" name="claim_hash" value="<?= htmlspecialchars($c['claim_hash']) ?>">
            <button class="btn btn-success btn-sm">Mark Claimed</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body></html>
