<?php
require_once '../includes/auth_check.php';
checkAuth('user');
require_once '../config/db.php';
$page_title = 'Order Confirmed – Paperly';

$uid      = (int)$_SESSION['user_id'];
$order_id = (int)($_GET['order_id'] ?? 0);

if (!$order_id) {
    header('Location: products.php');
    exit;
}

// Fetch order — must belong to this user
$order = $conn->query("
    SELECT o.*, u.full_name as user_name
    FROM orders o
    
    JOIN users u ON o.user_id = u.id
    WHERE o.id = $order_id AND o.user_id = $uid
")->fetch_assoc();

if (!$order) {
    header('Location: products.php');
    exit;
}

// Fetch order items
$items_res = $conn->query("
    SELECT oi.quantity, oi.price, p.name, p.image, p.category_id
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
");
$order_items = [];
while ($row = $items_res->fetch_assoc()) $order_items[] = $row;

// Decode delivery address
$addr = json_decode($order['delivery_address'], true) ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $page_title ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet"/>
  <style>
    * { box-sizing: border-box; }
    body { font-family: 'DM Sans', sans-serif; background: #f5f9ff; }
    .serif { font-family: 'DM Serif Display', serif; }

    @keyframes popIn {
      0%   { transform: scale(0.5); opacity: 0; }
      70%  { transform: scale(1.1); }
      100% { transform: scale(1); opacity: 1; }
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .success-icon { animation: popIn 0.6s ease both; }
    .fade-up-1 { animation: fadeUp 0.5s 0.3s ease both; opacity: 0; }
    .fade-up-2 { animation: fadeUp 0.5s 0.5s ease both; opacity: 0; }
    .fade-up-3 { animation: fadeUp 0.5s 0.7s ease both; opacity: 0; }

    .info-row {
      display: flex; justify-content: space-between;
      font-size: 13px; padding: 8px 0;
      border-bottom: 1px solid #f0f8ff;
    }
    .info-row:last-child { border-bottom: none; }

    .status-badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: #fef9c3; color: #854d0e;
      border-radius: 50px; padding: 4px 12px;
      font-size: 12px; font-weight: 600;
    }

    .cart-thumb {
      width: 44px; height: 44px; background: #EBF5FF;
      border-radius: 10px; display: flex; align-items: center;
      justify-content: center; font-size: 18px; overflow: hidden; flex-shrink: 0;
    }
  </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<main class="u-main">

  <!-- Success Header -->
  <div style="text-align:center;padding:32px 0 28px;" class="fade-up-1">
    <div class="success-icon" style="font-size:72px;margin-bottom:16px;">🎉</div>
    <h1 class="serif" style="font-size:32px;color:#0D3E6E;margin-bottom:8px;">Order Placed!</h1>
    <p style="color:#64748b;font-size:15px;">
      Thank you, <strong><?= htmlspecialchars($addr['full_name'] ?? $order['user_name']) ?></strong>! Your order is confirmed.
    </p>
    <div style="margin-top:12px;">
      <span class="status-badge">
        <span style="width:7px;height:7px;background:#eab308;border-radius:50%;display:inline-block;"></span>
        Order Pending
      </span>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;" class="fade-up-2">

    <!-- Left: Order Details -->
    <div style="display:flex;flex-direction:column;gap:18px;">

      <!-- Order ID + Date -->
      <div style="background:#fff;border:1px solid #dbeeff;border-radius:18px;padding:22px;">
        <h2 class="serif" style="font-size:17px;color:#0D3E6E;margin-bottom:14px;">Order Details</h2>
        <div class="info-row">
          <span style="color:#64748b;">Order ID</span>
          <span style="font-weight:700;color:#0D3E6E;">#<?= str_pad($order_id, 5, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div class="info-row">
          <span style="color:#64748b;">Date</span>
          <span style="font-weight:600;"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="info-row">
          <span style="color:#64748b;">Payment</span>
          <span style="font-weight:600;text-transform:capitalize;"><?= htmlspecialchars($order['payment_method']) ?></span>
        </div>
        <div class="info-row">
          <span style="color:#64748b;">Payment Status</span>
          <span style="font-weight:600;color:#854d0e;text-transform:capitalize;"><?= htmlspecialchars($order['payment_status']) ?></span>
        </div>
      </div>

      <!-- Delivery Address -->
      <div style="background:#fff;border:1px solid #dbeeff;border-radius:18px;padding:22px;">
        <h2 class="serif" style="font-size:17px;color:#0D3E6E;margin-bottom:14px;">
          📍 Delivery Address
        </h2>
        <p style="font-weight:600;color:#0D3E6E;font-size:14px;"><?= htmlspecialchars($addr['full_name'] ?? '') ?></p>
        <p style="color:#64748b;font-size:13px;margin-top:4px;line-height:1.6;">
          <?= htmlspecialchars($addr['address'] ?? '') ?><br>
          <?= htmlspecialchars($addr['city'] ?? '') ?><?= !empty($addr['district']) ? ', ' . htmlspecialchars($addr['district']) : '' ?><br>
          📞 <?= htmlspecialchars($addr['phone'] ?? '') ?>
        </p>
        <?php if (!empty($addr['note'])): ?>
        <p style="margin-top:8px;font-size:12px;color:#94a3b8;background:#f8fbff;padding:8px 12px;border-radius:8px;">
          💬 <?= htmlspecialchars($addr['note']) ?>
        </p>
        <?php endif; ?>
      </div>

      <!-- Items Ordered -->
      <div style="background:#fff;border:1px solid #dbeeff;border-radius:18px;padding:22px;">
        <h2 class="serif" style="font-size:17px;color:#0D3E6E;margin-bottom:16px;">
          Items Ordered
        </h2>
        <div style="display:flex;flex-direction:column;gap:12px;">
          <?php
            $cat_emojis = [1=>'📓', 2=>'✒️', 3=>'🎨', 4=>'📎', 5=>'📅'];
            foreach ($order_items as $item):
          ?>
          <div style="display:flex;align-items:center;gap:12px;">
            <div class="cart-thumb">
              <?php if (!empty($item['image'])): ?>
                <img src="../assets/images/products/<?= htmlspecialchars(basename($item['image'])) ?>"
                     style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'">
              <?php else: ?>
                <?= $cat_emojis[(int)$item['category_id']] ?? '📦' ?>
              <?php endif; ?>
            </div>
            <div style="flex:1;">
              <p style="font-size:13px;font-weight:600;color:#0D3E6E;"><?= htmlspecialchars($item['name']) ?></p>
              <p style="font-size:12px;color:#94a3b8;">Qty: <?= $item['quantity'] ?> × Rs. <?= number_format($item['price']) ?></p>
            </div>
            <p style="font-size:14px;font-weight:700;color:#1A6FBA;">
              Rs. <?= number_format($item['price'] * $item['quantity']) ?>
            </p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

    <!-- Right: Payment Summary + Actions -->
    <div style="display:flex;flex-direction:column;gap:16px;" class="fade-up-3">

      <!-- Total -->
      <div style="background:#fff;border:1px solid #dbeeff;border-radius:18px;padding:22px;">
        <h2 class="serif" style="font-size:17px;color:#0D3E6E;margin-bottom:14px;">Payment Summary</h2>
        <div class="info-row">
          <span style="color:#64748b;">Subtotal</span>
          <span style="font-weight:600;">Rs. <?= number_format($order['total'] - $order['delivery_fee']) ?></span>
        </div>
        <div class="info-row">
          <span style="color:#64748b;">Delivery</span>
          <span style="font-weight:600;color:<?= $order['delivery_fee']==0?'#16a34a':'#dc2626' ?>;">
            <?= $order['delivery_fee']==0 ? 'FREE' : 'Rs. '.$order['delivery_fee'] ?>
          </span>
        </div>
        <div style="display:flex;justify-content:space-between;padding-top:12px;margin-top:4px;border-top:2px solid #e8f3ff;">
          <span style="font-weight:700;color:#0D3E6E;font-size:15px;">Total Paid</span>
          <span style="font-weight:700;color:#0D3E6E;font-size:16px;">Rs. <?= number_format($order['total']) ?></span>
        </div>
      </div>

      <!-- Actions -->
      <div style="background:#fff;border:1px solid #dbeeff;border-radius:18px;padding:22px;display:flex;flex-direction:column;gap:10px;">
        <a href="orders.php"
           style="display:block;text-align:center;background:#0D3E6E;color:#fff;border-radius:50px;padding:13px;font-weight:600;font-size:14px;text-decoration:none;transition:background 0.2s;"
           onmouseover="this.style.background='#3B9EF5'" onmouseout="this.style.background='#0D3E6E'">
          View My Orders
        </a>
        <a href="products.php"
           style="display:block;text-align:center;background:#EBF5FF;color:#1A6FBA;border:1.5px solid #93CBFF;border-radius:50px;padding:12px;font-weight:600;font-size:14px;text-decoration:none;">
          Continue Shopping
        </a>
      </div>

      <!-- What's next -->
      <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:14px;padding:16px;">
        <p style="font-weight:600;color:#15803d;font-size:13px;margin-bottom:8px;">✅ What happens next?</p>
        <ul style="color:#166534;font-size:12px;line-height:2;padding-left:4px;list-style:none;">
          <li>📦 We'll prepare your order</li>
          <li>🚚 Delivery within 1–3 business days</li>
          <li><?= $order['payment_method']==='cod' ? '💵 Pay cash on delivery' : '✅ Payment confirmed' ?></li>
        </ul>
      </div>

    </div>
  </div>

</main>
</body>
</html>