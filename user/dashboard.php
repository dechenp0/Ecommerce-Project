<?php
require_once '../includes/auth_check.php';
checkAuth('user');
require_once '../config/db.php';
$page_title = 'Dashboard – Paperly';

$uid = (int)$_SESSION['user_id'];

$order_res   = $conn->query("SELECT COUNT(*) as total, SUM(total) as spent FROM orders WHERE user_id = $uid");
$order_stats = $order_res->fetch_assoc();
$cart_res    = $conn->query("SELECT SUM(quantity) as items FROM cart WHERE user_id = $uid");
$cart_items  = $cart_res->fetch_assoc()['items'] ?? 0;
$recent      = $conn->query("SELECT o.id, o.total, o.status, o.created_at FROM orders o WHERE o.user_id = $uid ORDER BY o.created_at DESC LIMIT 5");
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

  <!-- Header -->
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;">
    <div>
      <h1 class="serif" style="font-size:28px;color:#0D3E6E;line-height:1.2;">Hello, <?= htmlspecialchars(explode(' ',$_SESSION['full_name'])[0]) ?> 👋</h1>
      <p style="color:#94a3b8;font-size:14px;margin-top:4px;"><?= date('l, F j, Y') ?> — Welcome back to Paperly</p>
    </div>
    <a href="products.php" style="background:#0D3E6E;color:#fff;border-radius:50px;padding:11px 24px;font-weight:600;font-size:14px;text-decoration:none;display:flex;align-items:center;gap:8px;transition:background 0.2s;"
       onmouseover="this.style.background='#3B9EF5'" onmouseout="this.style.background='#0D3E6E'">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
      Shop Now
    </a>
  </div>

  <!-- Stat cards -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:28px;">

    <!-- Orders -->
    <div style="background:#fff;border:1px solid #dbeeff;border-radius:20px;padding:24px;position:relative;overflow:hidden;">
      <div style="position:absolute;top:-16px;right:-16px;width:80px;height:80px;background:#EBF5FF;border-radius:50%;opacity:0.6;"></div>
      <div style="width:42px;height:42px;background:#EBF5FF;border-radius:13px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
        <svg width="20" height="20" fill="none" stroke="#3B9EF5" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      </div>
      <p style="font-size:30px;font-weight:700;color:#0D3E6E;line-height:1;"><?= $order_stats['total'] ?? 0 ?></p>
      <p style="font-size:13px;color:#94a3b8;margin-top:5px;font-weight:500;">Total Orders</p>
    </div>

    <!-- Spent -->
    <div style="background:linear-gradient(135deg,#0D3E6E 0%,#1A6FBA 100%);border-radius:20px;padding:24px;position:relative;overflow:hidden;">
      <div style="position:absolute;top:-20px;right:-20px;width:90px;height:90px;background:rgba(255,255,255,0.06);border-radius:50%;"></div>
      <div style="width:42px;height:42px;background:rgba(255,255,255,0.12);border-radius:13px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
        <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
      </div>
      <p style="font-size:26px;font-weight:700;color:#fff;line-height:1;">Rs. <?= number_format($order_stats['spent'] ?? 0) ?></p>
      <p style="font-size:13px;color:rgba(255,255,255,0.55);margin-top:5px;font-weight:500;">Total Spent</p>
    </div>

    <!-- Cart -->
    <div style="background:#fff;border:1px solid #dbeeff;border-radius:20px;padding:24px;position:relative;overflow:hidden;">
      <div style="position:absolute;top:-16px;right:-16px;width:80px;height:80px;background:#EBF5FF;border-radius:50%;opacity:0.6;"></div>
      <div style="width:42px;height:42px;background:#EBF5FF;border-radius:13px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
        <svg width="20" height="20" fill="none" stroke="#3B9EF5" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
      </div>
      <p style="font-size:30px;font-weight:700;color:#0D3E6E;line-height:1;"><?= $cart_items ?></p>
      <p style="font-size:13px;color:#94a3b8;margin-top:5px;font-weight:500;">Items in Cart</p>
    </div>
  </div>

  <!-- Quick actions -->
  <div style="display:flex;gap:12px;margin-bottom:28px;flex-wrap:wrap;">
    <a href="products.php" style="background:#3B9EF5;color:#fff;border-radius:50px;padding:10px 22px;font-weight:600;font-size:13px;text-decoration:none;">🛍️ Browse Products</a>
    <a href="cart.php" style="border:1.5px solid #3B9EF5;color:#3B9EF5;border-radius:50px;padding:9px 22px;font-weight:600;font-size:13px;text-decoration:none;background:#fff;">🛒 View Cart</a>
    <a href="orders.php" style="border:1.5px solid #d9eeff;color:#64748b;border-radius:50px;padding:9px 22px;font-weight:600;font-size:13px;text-decoration:none;background:#fff;">📦 My Orders</a>
  </div>

  <!-- Recent orders table -->
  <div style="background:#fff;border:1px solid #dbeeff;border-radius:20px;overflow:hidden;">
    <div style="padding:20px 26px;border-bottom:1px solid #e8f3ff;display:flex;align-items:center;justify-content:space-between;">
      <h2 class="serif" style="font-size:20px;color:#0D3E6E;">Recent Orders</h2>
      <a href="orders.php" style="color:#3B9EF5;font-size:13px;font-weight:600;text-decoration:none;">View all →</a>
    </div>
    <?php if ($recent->num_rows > 0): ?>
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <thead style="background:#F7FBFF;">
        <tr>
          <th style="padding:12px 26px;text-align:left;color:#64748b;font-weight:500;font-size:13px;">Order #</th>
          <th style="padding:12px 26px;text-align:left;color:#64748b;font-weight:500;font-size:13px;">Date</th>
          <th style="padding:12px 26px;text-align:left;color:#64748b;font-weight:500;font-size:13px;">Total</th>
          <th style="padding:12px 26px;text-align:left;color:#64748b;font-weight:500;font-size:13px;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($ord = $recent->fetch_assoc()):
          $sc = ['pending'=>['#fef9c3','#854d0e'],'processing'=>['#dbeafe','#1e40af'],'shipped'=>['#e0f2fe','#0369a1'],'delivered'=>['#dcfce7','#15803d'],'cancelled'=>['#fee2e2','#dc2626']];
          [$bg,$tc] = $sc[$ord['status']] ?? ['#f1f5f9','#475569'];
        ?>
        <tr style="border-top:1px solid #f0f8ff;transition:background 0.15s;" onmouseover="this.style.background='#fafeff'" onmouseout="this.style.background=''">
          <td style="padding:14px 26px;font-weight:700;color:#0D3E6E;">#<?= $ord['id'] ?></td>
          <td style="padding:14px 26px;color:#64748b;"><?= date('M d, Y', strtotime($ord['created_at'])) ?></td>
          <td style="padding:14px 26px;font-weight:700;color:#1A6FBA;">Rs. <?= number_format($ord['total']) ?></td>
          <td style="padding:14px 26px;">
            <span style="background:<?=$bg?>;color:<?=$tc?>;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;text-transform:capitalize;"><?= $ord['status'] ?></span>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div style="padding:56px;text-align:center;color:#94a3b8;">
      <p style="font-size:44px;margin-bottom:14px;">🛍️</p>
      <p style="font-weight:600;color:#0D3E6E;margin-bottom:6px;">No orders yet</p>
      <p style="font-size:13px;margin-bottom:18px;">Start shopping and your orders will appear here</p>
      <a href="products.php" style="background:#3B9EF5;color:#fff;border-radius:50px;padding:10px 24px;font-weight:600;font-size:13px;text-decoration:none;">Browse Products</a>
    </div>
    <?php endif; ?>
  </div>

</main>
</body>
</html>