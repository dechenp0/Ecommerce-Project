<?php
require_once '../includes/auth_check.php';
checkAuth('user');
require_once '../config/db.php';
$page_title = 'Dashboard – Paperly';
 
$uid = (int)$_SESSION['user_id'];
 
// Stats
$order_res   = $conn->query("SELECT COUNT(*) as total, SUM(total) as spent FROM orders WHERE user_id = $uid");
$order_stats = $order_res->fetch_assoc();
$cart_res    = $conn->query("SELECT SUM(quantity) as items FROM cart WHERE user_id = $uid");
$cart_items  = $cart_res->fetch_assoc()['items'] ?? 0;
 
// Recent orders
$recent = $conn->query("SELECT o.id, o.total, o.status, o.created_at FROM orders o WHERE o.user_id = $uid ORDER BY o.created_at DESC LIMIT 5");
 
include '../includes/header.php';
?>
<div class="max-w-6xl mx-auto px-6 py-10">
 
  <!-- Greeting -->
  <div class="mb-8">
    <h1 class="serif text-3xl" style="color:#0D3E6E;">Hello, <?= htmlspecialchars($_SESSION['full_name']) ?> 👋</h1>
    <p class="text-gray-400 text-sm mt-1">Welcome back to your Paperly dashboard.</p>
  </div>
 
  <!-- Stat cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">
    <div style="background:#fff;border:1px solid #d9eeff;border-radius:16px;padding:24px;">
      <div style="width:40px;height:40px;background:var(--blue-100);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
        <svg width="20" height="20" fill="none" stroke="#3B9EF5" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      </div>
      <p class="text-2xl font-bold" style="color:#0D3E6E;"><?= $order_stats['total'] ?? 0 ?></p>
      <p class="text-sm text-gray-400 mt-0.5">Total Orders</p>
    </div>
    <div style="background:#fff;border:1px solid #d9eeff;border-radius:16px;padding:24px;">
      <div style="width:40px;height:40px;background:var(--blue-100);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
        <svg width="20" height="20" fill="none" stroke="#3B9EF5" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
      </div>
      <p class="text-2xl font-bold" style="color:#0D3E6E;">Rs. <?= number_format($order_stats['spent'] ?? 0) ?></p>
      <p class="text-sm text-gray-400 mt-0.5">Total Spent</p>
    </div>
    <div style="background:#fff;border:1px solid #d9eeff;border-radius:16px;padding:24px;">
      <div style="width:40px;height:40px;background:var(--blue-100);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
        <svg width="20" height="20" fill="none" stroke="#3B9EF5" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
      </div>
      <p class="text-2xl font-bold" style="color:#0D3E6E;"><?= $cart_items ?></p>
      <p class="text-sm text-gray-400 mt-0.5">Items in Cart</p>
    </div>
  </div>
 
  <!-- Quick actions -->
  <div class="flex flex-wrap gap-3 mb-10">
    <a href="products.php" style="background:var(--blue-400);color:#fff;border-radius:50px;padding:10px 22px;font-weight:600;font-size:14px;text-decoration:none;display:inline-block;">Browse Products</a>
    <a href="cart.php"     style="border:1.5px solid var(--blue-400);color:var(--blue-600);border-radius:50px;padding:9px 22px;font-weight:600;font-size:14px;text-decoration:none;display:inline-block;background:#fff;">View Cart</a>
    <a href="orders.php"   style="border:1.5px solid #d9eeff;color:#64748b;border-radius:50px;padding:9px 22px;font-weight:600;font-size:14px;text-decoration:none;display:inline-block;background:#fff;">My Orders</a>
  </div>
 
  <!-- Recent orders table -->
  <div style="background:#fff;border:1px solid #d9eeff;border-radius:18px;overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:1px solid #e2f0ff;display:flex;align-items:center;justify-content:space-between;">
      <h2 class="serif text-xl" style="color:#0D3E6E;">Recent Orders</h2>
      <a href="orders.php" style="color:var(--blue-400);font-size:13px;font-weight:600;text-decoration:none;">View all →</a>
    </div>
    <?php if ($recent->num_rows > 0): ?>
    <table style="width:100%;border-collapse:collapse;font-size:14px;">
      <thead style="background:#F7FBFF;">
        <tr>
          <th style="padding:12px 24px;text-align:left;color:#64748b;font-weight:500;">Order #</th>
          <th style="padding:12px 24px;text-align:left;color:#64748b;font-weight:500;">Date</th>
          <th style="padding:12px 24px;text-align:left;color:#64748b;font-weight:500;">Total</th>
          <th style="padding:12px 24px;text-align:left;color:#64748b;font-weight:500;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($ord = $recent->fetch_assoc()):
          $status_colors = [
            'pending'    => ['#fef9c3','#854d0e'],
            'processing' => ['#dbeafe','#1e40af'],
            'shipped'    => ['#e0f2fe','#0369a1'],
            'delivered'  => ['#dcfce7','#15803d'],
            'cancelled'  => ['#fee2e2','#dc2626'],
          ];
          [$bg, $tc] = $status_colors[$ord['status']] ?? ['#f1f5f9','#475569'];
        ?>
        <tr style="border-top:1px solid #f0f8ff;">
          <td style="padding:14px 24px;font-weight:600;color:#0D3E6E;">#<?= $ord['id'] ?></td>
          <td style="padding:14px 24px;color:#64748b;"><?= date('M d, Y', strtotime($ord['created_at'])) ?></td>
          <td style="padding:14px 24px;font-weight:600;">Rs. <?= number_format($ord['total']) ?></td>
          <td style="padding:14px 24px;">
            <span style="background:<?= $bg ?>;color:<?= $tc ?>;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;text-transform:capitalize;">
              <?= $ord['status'] ?>
            </span>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div style="padding:48px;text-align:center;color:#94a3b8;">
      <p style="font-size:40px;margin-bottom:12px;">🛍️</p>
      <p style="font-weight:500;">No orders yet</p>
      <a href="products.php" style="color:var(--blue-400);font-size:13px;">Start shopping →</a>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php include '../includes/footer.php'; ?>