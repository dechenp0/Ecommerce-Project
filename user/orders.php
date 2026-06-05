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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $page_title ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet"/>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<main class="u-main">
  <div style="margin-bottom:32px;">
    <h1 class="serif" style="font-size:28px;color:#0D3E6E;">My Orders</h1>
    <p style="color:#94a3b8;font-size:14px;margin-top:4px;">Track and review all your past purchases</p>
  </div>

  <?php if ($orders->num_rows === 0): ?>
  <div style="background:#fff;border:1px solid #dbeeff;border-radius:20px;padding:72px;text-align:center;">
    <p style="font-size:52px;margin-bottom:16px;">📦</p>
    <p style="font-weight:700;font-size:18px;color:#0D3E6E;margin-bottom:6px;">No orders yet</p>
    <p style="color:#94a3b8;font-size:14px;margin-bottom:22px;">Once you place an order, it'll show up here.</p>
    <a href="products.php" style="background:#3B9EF5;color:#fff;border-radius:50px;padding:11px 26px;font-weight:600;font-size:14px;text-decoration:none;">Start Shopping</a>
  </div>

  <?php else: ?>
  <div style="display:flex;flex-direction:column;gap:14px;">
    <?php while ($o = $orders->fetch_assoc()):
      $sc = ['pending'=>['#fef9c3','#854d0e'],'processing'=>['#dbeafe','#1e40af'],'shipped'=>['#e0f2fe','#0369a1'],'delivered'=>['#dcfce7','#15803d'],'cancelled'=>['#fee2e2','#dc2626']];
      [$bg,$tc] = $sc[$o['status']] ?? ['#f1f5f9','#475569'];
      $icons = ['pending'=>'⏳','processing'=>'⚙️','shipped'=>'🚚','delivered'=>'✅','cancelled'=>'❌'];
      $icon = $icons[$o['status']] ?? '📦';
    ?>
    <div style="background:#fff;border:1px solid #dbeeff;border-radius:18px;padding:20px 26px;display:flex;align-items:center;gap:18px;transition:box-shadow 0.2s,transform 0.2s;"
         onmouseover="this.style.boxShadow='0 8px 30px rgba(59,158,245,0.1)';this.style.transform='translateY(-2px)'"
         onmouseout="this.style.boxShadow='';this.style.transform=''">

      <!-- Icon -->
      <div style="width:52px;height:52px;background:#EBF5FF;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
        <?= $icon ?>
      </div>

      <!-- Details -->
      <div style="flex:1;min-width:0;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:5px;flex-wrap:wrap;">
          <span style="font-weight:700;color:#0D3E6E;font-size:15px;">#<?= $o['id'] ?></span>
          <span style="background:<?=$bg?>;color:<?=$tc?>;padding:2px 12px;border-radius:20px;font-size:11px;font-weight:600;text-transform:capitalize;"><?= $o['status'] ?></span>
        </div>
        <p style="font-size:13px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:3px;"><?= htmlspecialchars($o['items'] ?? '—') ?></p>
        <p style="font-size:12px;color:#94a3b8;"><?= date('F j, Y', strtotime($o['created_at'])) ?></p>
      </div>

      <!-- Amount -->
      <div style="text-align:right;flex-shrink:0;">
        <p style="font-weight:700;font-size:18px;color:#1A6FBA;">Rs. <?= number_format($o['total']) ?></p>
        <a href="order_detail.php?id=<?= $o['id'] ?>" style="font-size:12px;color:#3B9EF5;text-decoration:none;font-weight:500;">View details →</a>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
  <?php endif; ?>
</main>
</body>
</html>