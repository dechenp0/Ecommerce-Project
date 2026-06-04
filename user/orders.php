<?php
require_once '../includes/auth_check.php';
checkAuth('user');
require_once '../config/db.php';
$page_title = 'My Orders – Paperly';
 
$uid    = (int)$_SESSION['user_id'];
$orders = $conn->query("
    SELECT o.id, o.total, o.status, o.created_at,
           GROUP_CONCAT(p.name ORDER BY p.name SEPARATOR ', ') as items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p     ON oi.product_id = p.id
    WHERE o.user_id = $uid
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
 
include '../includes/header.php';
?>
<div class="max-w-4xl mx-auto px-6 py-10">
  <h1 class="serif text-3xl mb-8" style="color:#0D3E6E;">My Orders</h1>
 
  <?php if ($orders->num_rows === 0): ?>
  <div style="background:#fff;border:1px solid #d9eeff;border-radius:18px;padding:64px;text-align:center;">
    <p style="font-size:48px;margin-bottom:12px;">📦</p>
    <p style="font-weight:600;color:#0D3E6E;margin-bottom:6px;">No orders yet</p>
    <a href="products.php" style="color:#3B9EF5;font-size:14px;">Start shopping →</a>
  </div>
  <?php else: ?>
  <div style="display:flex;flex-direction:column;gap:14px;">
    <?php while ($o = $orders->fetch_assoc()):
      $sc = ['pending'=>['#fef9c3','#854d0e'],'processing'=>['#dbeafe','#1e40af'],'shipped'=>['#e0f2fe','#0369a1'],'delivered'=>['#dcfce7','#15803d'],'cancelled'=>['#fee2e2','#dc2626']];
      [$bg,$tc] = $sc[$o['status']] ?? ['#f1f5f9','#475569'];
    ?>
    <div style="background:#fff;border:1px solid #d9eeff;border-radius:16px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;">
      <div style="flex:1;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
          <span style="font-weight:700;color:#0D3E6E;font-size:15px;">#<?= $o['id'] ?></span>
          <span style="background:<?=$bg?>;color:<?=$tc?>;padding:2px 12px;border-radius:20px;font-size:12px;font-weight:600;text-transform:capitalize;"><?= $o['status'] ?></span>
        </div>
        <p style="font-size:13px;color:#64748b;margin-bottom:3px;"><?= htmlspecialchars($o['items'] ?? '—') ?></p>
        <p style="font-size:12px;color:#94a3b8;"><?= date('F j, Y', strtotime($o['created_at'])) ?></p>
      </div>
      <div style="text-align:right;flex-shrink:0;">
        <p style="font-weight:700;font-size:17px;color:#1A6FBA;">Rs. <?= number_format($o['total']) ?></p>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
  <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>