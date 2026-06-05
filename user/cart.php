<?php
require_once '../includes/auth_check.php';
checkAuth('user');
require_once '../config/db.php';
$page_title = 'My Cart – Paperly';

$uid = (int)$_SESSION['user_id'];
$res = $conn->query("
    SELECT c.id as cart_id, c.quantity, p.id as product_id,
           p.name, p.price, p.image, p.stock, cat.name as category, p.category_id
    FROM cart c
    JOIN products p ON c.product_id = p.id
    LEFT JOIN categories cat ON p.category_id = cat.id
    WHERE c.user_id = $uid
    ORDER BY c.id DESC
");

$items = [];
$total = 0;
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
    $total  += $row['price'] * $row['quantity'];
}
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

    .qty-wrapper {
      display: flex; align-items: center;
      background: #EBF5FF; border: 1.5px solid #d9eeff;
      border-radius: 50px; padding: 3px; width: fit-content;
    }
    .qty-btn {
      width: 30px; height: 30px; border-radius: 50%; border: none;
      background: transparent; font-size: 18px; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      color: #1A6FBA; transition: background 0.15s;
      font-family: 'DM Sans', sans-serif; line-height: 1; padding: 0; flex-shrink: 0;
    }
    .qty-btn:hover { background: #fff; }
    .qty-btn:disabled { opacity: 0.4; cursor: not-allowed; }
    .qty-btn:active:not(:disabled) { transform: scale(0.92); }
    .qty-btn.loading { pointer-events: none; opacity: 0.5; }

    .qty-display {
      font-weight: 700; min-width: 28px; text-align: center;
      font-size: 15px; color: #0D3E6E; user-select: none;
    }

    .cart-item {
      background: #fff; border: 1px solid #dbeeff; border-radius: 18px;
      padding: 20px; display: flex; gap: 16px; align-items: center;
      transition: box-shadow 0.2s, opacity 0.35s, transform 0.35s;
    }
    .cart-item:hover { box-shadow: 0 6px 24px rgba(59,158,245,0.1); }
    .cart-item.removing { opacity: 0; transform: translateX(30px); }

    .remove-btn {
      color: #ef4444; font-size: 12px; background: none; border: none;
      cursor: pointer; font-family: 'DM Sans', sans-serif; font-weight: 500;
      padding: 0; display: flex; align-items: center; gap: 3px; transition: opacity 0.15s;
    }
    .remove-btn:hover { text-decoration: underline; opacity: 0.8; }

    .empty-cart {
      background: #fff; border: 1px solid #dbeeff; border-radius: 20px;
      padding: 72px; text-align: center; display: none;
    }
    .empty-cart.show { display: block; }

    #toast {
      position: fixed; bottom: 28px; right: 28px; z-index: 9999;
      background: #0D3E6E; color: #fff; padding: 12px 22px;
      border-radius: 12px; font-size: 14px; font-weight: 500;
      transform: translateY(80px); opacity: 0;
      transition: transform 0.3s, opacity 0.3s; pointer-events: none;
      box-shadow: 0 8px 24px rgba(13,62,110,0.25);
    }
  </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<main class="u-main">
  <div style="margin-bottom:28px;">
    <h1 class="serif" style="font-size:28px;color:#0D3E6E;">
      My Cart
      <span id="itemCountLabel" style="font-size:15px;color:#94a3b8;font-family:'DM Sans',sans-serif;font-weight:400;margin-left:8px;">
        (<?= count($items) ?> item<?= count($items) != 1 ? 's' : '' ?>)
      </span>
    </h1>
    <p style="color:#94a3b8;font-size:14px;margin-top:4px;">Review your items before checking out</p>
  </div>

  <!-- Empty state -->
  <div class="empty-cart <?= empty($items) ? 'show' : '' ?>" id="emptyState">
    <p style="font-size:52px;margin-bottom:16px;">🛒</p>
    <p style="font-weight:700;font-size:18px;color:#0D3E6E;margin-bottom:6px;">Your cart is empty</p>
    <p style="color:#94a3b8;font-size:14px;margin-bottom:22px;">Add some products to get started</p>
    <a href="products.php" style="background:#3B9EF5;color:#fff;border-radius:50px;padding:11px 26px;font-weight:600;font-size:14px;text-decoration:none;">Browse Products</a>
  </div>

  <?php if (!empty($items)): ?>
  <div id="cartLayout" style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">

    <!-- Cart Items -->
    <div style="display:flex;flex-direction:column;gap:14px;" id="cartItems">
      <?php
        $cat_emojis = [1=>'📓', 2=>'✒️', 3=>'🎨', 4=>'📎', 5=>'📅'];
        foreach ($items as $item):
      ?>
      <div class="cart-item" id="row-<?= $item['cart_id'] ?>" data-price="<?= (float)$item['price'] ?>">

        <!-- Image -->
        <div style="width:76px;height:76px;background:#EBF5FF;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:32px;flex-shrink:0;overflow:hidden;">
          <?php if (!empty($item['image'])): ?>
            <img src="../assets/images/products/<?= htmlspecialchars(basename($item['image'])) ?>"
                 alt="" style="width:100%;height:100%;object-fit:cover;"
                 onerror="this.style.display='none'">
          <?php else: ?>
            <?= $cat_emojis[(int)$item['category_id']] ?? '📦' ?>
          <?php endif; ?>
        </div>

        <!-- Details -->
        <div style="flex:1;min-width:0;">
          <p style="font-size:11px;color:#94a3b8;font-weight:500;text-transform:uppercase;letter-spacing:0.05em;">
            <?= htmlspecialchars($item['category'] ?? '') ?>
          </p>
          <p style="font-weight:600;color:#0D3E6E;font-size:15px;margin-top:2px;">
            <?= htmlspecialchars($item['name']) ?>
          </p>
          <p style="color:#1A6FBA;font-weight:700;font-size:14px;margin-top:4px;">
            Rs. <?= number_format($item['price']) ?> each
          </p>
        </div>

        <!-- Qty Controls -->
        <div style="flex-shrink:0;">
          <div class="qty-wrapper">
            <button class="qty-btn" id="btn-minus-<?= $item['cart_id'] ?>"
                    onclick="changeQty(<?= $item['cart_id'] ?>, -1)" title="Decrease">−</button>
            <span class="qty-display" id="qty-<?= $item['cart_id'] ?>"><?= $item['quantity'] ?></span>
            <button class="qty-btn" id="btn-plus-<?= $item['cart_id'] ?>"
                    onclick="changeQty(<?= $item['cart_id'] ?>, +1)" title="Increase">+</button>
          </div>
        </div>

        <!-- Subtotal + Remove -->
        <div style="text-align:right;flex-shrink:0;min-width:100px;">
          <p id="sub-<?= $item['cart_id'] ?>" style="font-weight:700;font-size:16px;color:#0D3E6E;">
            Rs. <?= number_format($item['price'] * $item['quantity']) ?>
          </p>
          <button class="remove-btn" onclick="removeItem(<?= $item['cart_id'] ?>)" style="margin-top:6px;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
              <path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/>
            </svg>
            Remove
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Order Summary -->
    <div style="background:#fff;border:1px solid #dbeeff;border-radius:20px;padding:26px;position:sticky;top:32px;">
      <h2 class="serif" style="font-size:20px;color:#0D3E6E;margin-bottom:20px;">Order Summary</h2>

      <div style="display:flex;justify-content:space-between;font-size:14px;color:#64748b;margin-bottom:10px;">
        <span>Subtotal</span>
        <span id="summarySubtotal" style="font-weight:600;color:#1a2235;">Rs. <?= number_format($total) ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:6px;">
        <span style="color:#64748b;">Delivery</span>
        <span id="deliveryText" style="font-weight:600;color:<?= $total >= 500 ? '#16a34a' : '#dc2626' ?>;">
          <?= $total >= 500 ? 'FREE' : 'Rs. 80' ?>
        </span>
      </div>
      <p id="freeShipNote" style="font-size:11px;margin-bottom:10px;color:<?= $total >= 500 ? '#16a34a' : '#94a3b8' ?>;">
        <?= $total >= 500 ? '🎉 You qualify for free delivery!' : 'Add Rs. ' . number_format(500 - $total) . ' more for free delivery' ?>
      </p>

      <div style="height:1px;background:#e8f3ff;margin:14px 0;"></div>

      <div style="display:flex;justify-content:space-between;font-weight:700;color:#0D3E6E;font-size:16px;margin-bottom:22px;">
        <span>Total</span>
        <span id="summaryTotal">Rs. <?= number_format($total >= 500 ? $total : $total + 80) ?></span>
      </div>

      <a href="checkout.php"
         style="display:block;text-align:center;background:#0D3E6E;color:#fff;border-radius:50px;padding:14px;font-weight:600;font-size:15px;text-decoration:none;transition:background 0.2s;"
         onmouseover="this.style.background='#3B9EF5'" onmouseout="this.style.background='#0D3E6E'">
        Proceed to Checkout →
      </a>
      <a href="products.php" style="display:block;text-align:center;color:#94a3b8;font-size:13px;margin-top:12px;text-decoration:none;">
        ← Continue Shopping
      </a>

      <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:20px;padding-top:16px;border-top:1px solid #f0f8ff;">
        <svg width="14" height="14" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        <span style="font-size:12px;color:#94a3b8;">Secure checkout</span>
      </div>
    </div>

  </div>
  <?php endif; ?>
</main>

<div id="toast"></div>

<script>
// Prices keyed by cart_id
const prices = {
  <?php foreach ($items as $i): ?>
  <?= $i['cart_id'] ?>: <?= (float)$i['price'] ?>,
  <?php endforeach; ?>
};

const pending = {};

function changeQty(cart_id, delta) {
  if (pending[cart_id]) return;
  const qtyEl   = document.getElementById('qty-' + cart_id);
  const current = parseInt(qtyEl.textContent.trim(), 10);
  const new_qty = current + delta;
  updateQty(cart_id, Math.max(0, new_qty));
}

function removeItem(cart_id) {
  if (pending[cart_id]) return;
  updateQty(cart_id, 0);
}

async function updateQty(cart_id, new_qty) {
  pending[cart_id] = true;
  setRowLoading(cart_id, true);

  try {
    const res = await fetch('../api/update-cart.php', {
      method : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body   : JSON.stringify({ action: 'update', cart_id, quantity: new_qty })
    });

    const text = await res.text(); // read as text first to debug

    let data;
    try {
      data = JSON.parse(text);
    } catch(e) {
      console.error('Non-JSON response:', text);
      showToast('⚠ Server error. Check console.', true);
      setRowLoading(cart_id, false);
      pending[cart_id] = false;
      return;
    }

    if (!data.success) {
      showToast('⚠ ' + (data.message || 'Error'), true);
      setRowLoading(cart_id, false);
      pending[cart_id] = false;
      return;
    }

    if (new_qty <= 0) {
      const row = document.getElementById('row-' + cart_id);
      if (row) {
        row.classList.add('removing');
        setTimeout(() => { row.remove(); checkEmptyState(); }, 350);
      }
      delete prices[cart_id];
      showToast('🗑 Item removed');
    } else {
      document.getElementById('qty-' + cart_id).textContent = new_qty;
      document.getElementById('sub-' + cart_id).textContent =
        'Rs. ' + formatNum(prices[cart_id] * new_qty);
      setRowLoading(cart_id, false);
      showToast('✓ Cart updated');
    }

    const badge = document.querySelector('.cart-badge');
    if (badge && data.cart_count !== undefined) badge.textContent = data.cart_count;

    updateSummary(data.cart_total ?? recalcTotal());

  } catch (err) {
    console.error('Fetch error:', err);
    showToast('⚠ Could not reach server.', true);
    setRowLoading(cart_id, false);
  }

  pending[cart_id] = false;
}

function setRowLoading(cart_id, on) {
  ['btn-minus-', 'btn-plus-'].forEach(prefix => {
    const btn = document.getElementById(prefix + cart_id);
    if (btn) { btn.disabled = on; btn.classList.toggle('loading', on); }
  });
}

function recalcTotal() {
  let t = 0;
  document.querySelectorAll('.cart-item').forEach(row => {
    const id  = row.id.replace('row-', '');
    const qty = parseInt(document.getElementById('qty-' + id)?.textContent ?? '0', 10);
    t += (prices[id] ?? 0) * qty;
  });
  return t;
}

function updateSummary(total_raw) {
  const total    = parseFloat(String(total_raw).replace(/,/g, ''));
  const delivery = total >= 500 ? 0 : 80;

  document.getElementById('summarySubtotal').textContent = 'Rs. ' + formatNum(total);
  document.getElementById('summaryTotal').textContent    = 'Rs. ' + formatNum(total + delivery);

  const dEl = document.getElementById('deliveryText');
  const nEl = document.getElementById('freeShipNote');
  if (delivery === 0) {
    dEl.textContent = 'FREE'; dEl.style.color = '#16a34a';
    nEl.textContent = '🎉 You qualify for free delivery!'; nEl.style.color = '#16a34a';
  } else {
    dEl.textContent = 'Rs. 80'; dEl.style.color = '#dc2626';
    nEl.textContent = 'Add Rs. ' + formatNum(500 - total) + ' more for free delivery';
    nEl.style.color = '#94a3b8';
  }

  const rowCount = document.querySelectorAll('.cart-item').length;
  const label    = document.getElementById('itemCountLabel');
  if (label) label.textContent = '(' + rowCount + ' item' + (rowCount !== 1 ? 's' : '') + ')';
}

function checkEmptyState() {
  const rows   = document.querySelectorAll('.cart-item');
  const layout = document.getElementById('cartLayout');
  const empty  = document.getElementById('emptyState');
  if (rows.length === 0) {
    if (layout) layout.style.display = 'none';
    if (empty)  empty.classList.add('show');
    updateSummary(0);
  }
  const label = document.getElementById('itemCountLabel');
  if (label) label.textContent = '(' + rows.length + ' item' + (rows.length !== 1 ? 's' : '') + ')';
}

let toastTimer;
function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.background = isError ? '#dc2626' : '#0D3E6E';
  t.style.transform  = 'translateY(0)';
  t.style.opacity    = '1';
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    t.style.transform = 'translateY(80px)';
    t.style.opacity   = '0';
  }, 2600);
}

function formatNum(n) {
  return Math.round(n).toLocaleString('en-IN');
}
</script>
</body>
</html>