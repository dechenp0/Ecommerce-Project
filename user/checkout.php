<?php
require_once '../includes/auth_check.php';
checkAuth('user');
require_once '../config/db.php';
$page_title = 'Checkout – Paperly';

$uid = (int)$_SESSION['user_id'];

// Fetch cart items
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

// Redirect if cart is empty
if (empty($items)) {
    header('Location: cart.php');
    exit;
}

$delivery = $total >= 500 ? 0 : 80;
$grand_total = $total + $delivery;

// Fetch user info
$user = $conn->query("SELECT * FROM users WHERE id = $uid")->fetch_assoc();
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

    .form-input {
      width: 100%;
      padding: 11px 14px;
      border: 1.5px solid #d9eeff;
      border-radius: 10px;
      font-size: 14px;
      font-family: 'DM Sans', sans-serif;
      outline: none;
      background: #fff;
      transition: border-color 0.2s, box-shadow 0.2s;
      color: #1a2235;
    }
    .form-input:focus {
      border-color: #3B9EF5;
      box-shadow: 0 0 0 3px rgba(59,158,245,0.1);
    }
    .form-label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: #64748b;
      margin-bottom: 5px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .payment-option {
      border: 1.5px solid #d9eeff;
      border-radius: 14px;
      padding: 14px 18px;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
      display: flex;
      align-items: center;
      gap: 12px;
      background: #fff;
    }
    .payment-option:hover { border-color: #3B9EF5; background: #f0f8ff; }
    .payment-option.selected { border-color: #3B9EF5; background: #EBF5FF; }
    .payment-option input[type="radio"] { accent-color: #3B9EF5; width: 16px; height: 16px; }

    .step-badge {
      width: 28px; height: 28px;
      background: #3B9EF5; color: #fff;
      border-radius: 50%; display: flex;
      align-items: center; justify-content: center;
      font-size: 13px; font-weight: 700;
      flex-shrink: 0;
    }

    .cart-thumb {
      width: 48px; height: 48px;
      background: #EBF5FF;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px; overflow: hidden; flex-shrink: 0;
    }

    #placeOrderBtn {
      background: #0D3E6E; color: #fff;
      border: none; border-radius: 50px;
      padding: 15px; font-size: 15px;
      font-weight: 700; width: 100%;
      cursor: pointer; font-family: 'DM Sans', sans-serif;
      transition: background 0.2s, transform 0.15s;
    }
    #placeOrderBtn:hover { background: #3B9EF5; transform: translateY(-1px); }
    #placeOrderBtn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    #toast {
      position: fixed; bottom: 28px; right: 28px; z-index: 9999;
      background: #0D3E6E; color: #fff;
      padding: 12px 22px; border-radius: 12px;
      font-size: 14px; font-weight: 500;
      transform: translateY(80px); opacity: 0;
      transition: transform 0.3s, opacity 0.3s;
      pointer-events: none;
      box-shadow: 0 8px 24px rgba(13,62,110,0.25);
    }
  </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<main class="u-main">

  <div style="margin-bottom:28px;">
    <h1 class="serif" style="font-size:28px;color:#0D3E6E;">Checkout</h1>
    <p style="color:#94a3b8;font-size:14px;margin-top:4px;">Almost there — just fill in your details</p>
  </div>

  <!-- Progress Steps -->
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:32px;">
    <div style="display:flex;align-items:center;gap:8px;">
      <div style="width:28px;height:28px;background:#3B9EF5;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;">1</div>
      <span style="font-size:13px;font-weight:600;color:#0D3E6E;">Delivery</span>
    </div>
    <div style="flex:1;height:2px;background:#d9eeff;max-width:60px;"></div>
    <div style="display:flex;align-items:center;gap:8px;">
      <div style="width:28px;height:28px;background:#3B9EF5;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;">2</div>
      <span style="font-size:13px;font-weight:600;color:#0D3E6E;">Payment</span>
    </div>
    <div style="flex:1;height:2px;background:#d9eeff;max-width:60px;"></div>
    <div style="display:flex;align-items:center;gap:8px;">
      <div style="width:28px;height:28px;background:#d9eeff;color:#94a3b8;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;" id="step3badge">3</div>
      <span style="font-size:13px;font-weight:500;color:#94a3b8;">Confirm</span>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

    <!-- LEFT: Form -->
    <div style="display:flex;flex-direction:column;gap:20px;">

      <!-- Delivery Address -->
      <div style="background:#fff;border:1px solid #dbeeff;border-radius:20px;padding:26px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
          <div class="step-badge">1</div>
          <h2 class="serif" style="font-size:18px;color:#0D3E6E;">Delivery Address</h2>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div>
            <label class="form-label">Full Name *</label>
            <input type="text" id="fullName" class="form-input"
                   value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                   placeholder="Your full name"/>
          </div>
          <div>
            <label class="form-label">Phone Number *</label>
            <input type="text" id="phone" class="form-input"
                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                   placeholder="98XXXXXXXX"/>
          </div>
        </div>

        <div style="margin-top:14px;">
          <label class="form-label">Street Address *</label>
          <input type="text" id="address" class="form-input" placeholder="House no., Street, Tole"/>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px;">
          <div>
            <label class="form-label">City *</label>
            <input type="text" id="city" class="form-input" placeholder="e.g. Kathmandu"/>
          </div>
          <div>
            <label class="form-label">District</label>
            <input type="text" id="district" class="form-input" placeholder="e.g. Bagmati"/>
          </div>
        </div>

        <div style="margin-top:14px;">
          <label class="form-label">Delivery Note (optional)</label>
          <textarea id="note" class="form-input" rows="2"
                    placeholder="Any instructions for delivery (e.g. call before arriving)"
                    style="resize:vertical;"></textarea>
        </div>
      </div>

      <!-- Payment Method -->
      <div style="background:#fff;border:1px solid #dbeeff;border-radius:20px;padding:26px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
          <div class="step-badge">2</div>
          <h2 class="serif" style="font-size:18px;color:#0D3E6E;">Payment Method</h2>
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;" id="paymentOptions">

          <label class="payment-option selected" onclick="selectPayment(this,'cod')">
            <input type="radio" name="payment" value="cod" checked/>
            <span style="font-size:22px;">💵</span>
            <div>
              <p style="font-weight:600;color:#0D3E6E;font-size:14px;">Cash on Delivery</p>
              <p style="font-size:12px;color:#94a3b8;">Pay when your order arrives</p>
            </div>
          </label>

          <label class="payment-option" onclick="selectPayment(this,'esewa')">
            <input type="radio" name="payment" value="esewa"/>
            <span style="font-size:22px;">🟢</span>
            <div>
              <p style="font-weight:600;color:#0D3E6E;font-size:14px;">eSewa</p>
              <p style="font-size:12px;color:#94a3b8;">Pay via eSewa digital wallet</p>
            </div>
          </label>

          <label class="payment-option" onclick="selectPayment(this,'khalti')">
            <input type="radio" name="payment" value="khalti"/>
            <span style="font-size:22px;">💜</span>
            <div>
              <p style="font-weight:600;color:#0D3E6E;font-size:14px;">Khalti</p>
              <p style="font-size:12px;color:#94a3b8;">Pay via Khalti digital wallet</p>
            </div>
          </label>

        </div>

        <!-- eSewa / Khalti info box -->
        <div id="digitalPayNote" style="display:none;background:#EBF5FF;border:1px solid #93CBFF;border-radius:12px;padding:12px 16px;margin-top:14px;font-size:13px;color:#1A6FBA;">
          📲 You'll be redirected to complete payment after placing your order.
        </div>
      </div>

    </div>

    <!-- RIGHT: Order Summary -->
    <div style="background:#fff;border:1px solid #dbeeff;border-radius:20px;padding:26px;position:sticky;top:32px;">
      <h2 class="serif" style="font-size:18px;color:#0D3E6E;margin-bottom:18px;">Order Summary</h2>

      <!-- Items list -->
      <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:18px;">
        <?php
          $cat_emojis = [1=>'📓', 2=>'✒️', 3=>'🎨', 4=>'📎', 5=>'📅'];
          foreach ($items as $item):
        ?>
        <div style="display:flex;align-items:center;gap:10px;">
          <div class="cart-thumb">
            <?php if (!empty($item['image'])): ?>
              <img src="../assets/images/products/<?= htmlspecialchars(basename($item['image'])) ?>"
                   style="width:100%;height:100%;object-fit:cover;"
                   onerror="this.style.display='none'">
            <?php else: ?>
              <?= $cat_emojis[(int)$item['category_id']] ?? '📦' ?>
            <?php endif; ?>
          </div>
          <div style="flex:1;min-width:0;">
            <p style="font-size:13px;font-weight:600;color:#0D3E6E;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              <?= htmlspecialchars($item['name']) ?>
            </p>
            <p style="font-size:12px;color:#94a3b8;">Qty: <?= $item['quantity'] ?></p>
          </div>
          <p style="font-size:13px;font-weight:700;color:#1A6FBA;flex-shrink:0;">
            Rs. <?= number_format($item['price'] * $item['quantity']) ?>
          </p>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="height:1px;background:#e8f3ff;margin-bottom:14px;"></div>

      <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:8px;">
        <span>Subtotal (<?= count($items) ?> items)</span>
        <span style="font-weight:600;color:#1a2235;">Rs. <?= number_format($total) ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;">
        <span style="color:#64748b;">Delivery</span>
        <span style="font-weight:600;color:<?= $delivery==0?'#16a34a':'#dc2626' ?>;">
          <?= $delivery==0 ? 'FREE' : 'Rs. '.$delivery ?>
        </span>
      </div>

      <div style="height:1px;background:#e8f3ff;margin:12px 0;"></div>

      <div style="display:flex;justify-content:space-between;font-weight:700;color:#0D3E6E;font-size:16px;margin-bottom:22px;">
        <span>Total</span>
        <span>Rs. <?= number_format($grand_total) ?></span>
      </div>

      <button id="placeOrderBtn" onclick="placeOrder()">
        Place Order →
      </button>

      <a href="cart.php" style="display:block;text-align:center;color:#94a3b8;font-size:13px;margin-top:12px;text-decoration:none;">
        ← Back to Cart
      </a>

      <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:18px;padding-top:14px;border-top:1px solid #f0f8ff;">
        <svg width="13" height="13" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span style="font-size:11px;color:#94a3b8;">100% Secure Checkout</span>
      </div>
    </div>

  </div>
</main>

<div id="toast"></div>

<script>
let selectedPayment = 'cod';

function selectPayment(label, method) {
  document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
  label.classList.add('selected');
  selectedPayment = method;

  const note = document.getElementById('digitalPayNote');
  note.style.display = (method === 'esewa' || method === 'khalti') ? 'block' : 'none';
}

async function placeOrder() {
  // Validate
  const fullName = document.getElementById('fullName').value.trim();
  const phone    = document.getElementById('phone').value.trim();
  const address  = document.getElementById('address').value.trim();
  const city     = document.getElementById('city').value.trim();

  if (!fullName || !phone || !address || !city) {
    showToast('⚠ Please fill in all required fields', true);
    return;
  }

  if (!/^[0-9]{10}$/.test(phone)) {
    showToast('⚠ Enter a valid 10-digit phone number', true);
    return;
  }

  const btn = document.getElementById('placeOrderBtn');
  btn.disabled = true;
  btn.textContent = 'Placing Order...';

  const payload = {
    full_name      : fullName,
    phone          : phone,
    address        : address,
    city           : city,
    district       : document.getElementById('district').value.trim(),
    note           : document.getElementById('note').value.trim(),
    payment_method : selectedPayment
  };

  try {
    const res  = await fetch('../api/place_order.php', {
      method : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body   : JSON.stringify(payload)
    });
    const data = await res.json();

    if (data.success) {
      // Redirect to confirmation page
      window.location.href = `order_confirmation.php?order_id=${data.order_id}`;
    } else {
      showToast('⚠ ' + (data.message || 'Could not place order'), true);
      btn.disabled = false;
      btn.textContent = 'Place Order →';
    }
  } catch (err) {
    showToast('⚠ Something went wrong. Please try again.', true);
    btn.disabled = false;
    btn.textContent = 'Place Order →';
  }
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
  }, 3000);
}
</script>
</body>
</html>