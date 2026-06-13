<?php
require_once '../includes/auth_check.php';
checkAuth('user');
require_once '../config/db.php';
$page_title = 'My Orders – Paperly';

$uid    = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order_id'])) {
    $del_oid = (int)$_POST['delete_order_id'];
    
    // Ensure order belongs to user
    $check = $conn->query("SELECT id FROM orders WHERE id = $del_oid AND user_id = $uid");
    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM order_items WHERE order_id = $del_oid");
        $conn->query("DELETE FROM orders WHERE id = $del_oid");
    }
}

$orders = $conn->query("
    SELECT o.id, o.total, o.status, o.payment_status, o.created_at,
           GROUP_CONCAT(p.name ORDER BY p.name SEPARATOR ', ') as items,
           MIN(p.image) as first_image
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

      <!-- Icon / Image -->
      <div style="width:52px;height:52px;background:#EBF5FF;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;overflow:hidden;">
        <?php if (!empty($o['first_image'])): ?>
          <img src="../assets/images/products/<?= htmlspecialchars(basename($o['first_image'])) ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
          <span style="display:none;"><?= $icon ?></span>
        <?php else: ?>
          <?= $icon ?>
        <?php endif; ?>
      </div>

      <!-- Details -->
      <div style="flex:1;min-width:0;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:5px;flex-wrap:wrap;">
          <span style="background:<?=$bg?>;color:<?=$tc?>;padding:2px 12px;border-radius:20px;font-size:11px;font-weight:600;text-transform:capitalize;"><?= $o['status'] ?></span>
          <?php 
            $pstat = $o['payment_status'] ?? 'pending';
            $pbg = $pstat === 'paid' ? '#dcfce7' : ($pstat === 'failed' ? '#fee2e2' : '#fef9c3');
            $ptc = $pstat === 'paid' ? '#15803d' : ($pstat === 'failed' ? '#dc2626' : '#854d0e');
          ?>
          <span style="background:<?= $pbg ?>;color:<?= $ptc ?>;padding:2px 12px;border-radius:20px;font-size:11px;font-weight:600;text-transform:capitalize;">Pay: <?= htmlspecialchars($pstat) ?></span>
        </div>
        <p style="font-size:13px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:3px;"><?= htmlspecialchars($o['items'] ?? '—') ?></p>
        <p style="font-size:12px;color:#94a3b8;"><?= date('F j, Y', strtotime($o['created_at'])) ?></p>
      </div>

      <!-- Amount -->
      <div style="text-align:right;flex-shrink:0;">
        <p style="font-weight:700;font-size:18px;color:#1A6FBA;">Rs. <?= number_format($o['total']) ?></p>
        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this order?');" style="margin-top:8px;">
          <input type="hidden" name="delete_order_id" value="<?= $o['id'] ?>"/>
          <button type="submit" style="background:#fee2e2;color:#dc2626;border:none;padding:5px 12px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;transition:background 0.2s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">Delete Order</button>
        </form>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
  <?php endif; ?>
</main>
</body>
</html>