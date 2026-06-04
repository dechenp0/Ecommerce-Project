<?php
require_once '../includes/auth_check.php';
checkAuth('user');
require_once '../config/db.php';
$page_title = 'My Cart – Paperly';
 
$uid = (int)$_SESSION['user_id'];
$res = $conn->query("
    SELECT c.id as cart_id, c.quantity, p.id as product_id,
           p.name, p.price, p.image, p.stock, cat.name as category
    FROM cart c
    JOIN products p   ON c.product_id  = p.id
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
 
include '../includes/header.php';
?>
<div class="max-w-5xl mx-auto px-6 py-10">
  <h1 class="serif text-3xl mb-8" style="color:#0D3E6E;">My Cart
    <span style="font-size:16px;color:#94a3b8;font-family:'DM Sans',sans-serif;font-weight:400;margin-left:8px;">(<?= count($items) ?> items)</span>
  </h1>
 
  <?php if (empty($items)): ?>
  <div style="background:#fff;border:1px solid #d9eeff;border-radius:18px;padding:64px;text-align:center;">
    <p style="font-size:48px;margin-bottom:12px;">🛒</p>
    <p style="font-weight:600;color:#0D3E6E;margin-bottom:6px;">Your cart is empty</p>
    <p style="color:#94a3b8;font-size:14px;margin-bottom:20px;">Add some products to get started</p>
    <a href="products.php" style="background:#3B9EF5;color:#fff;border-radius:50px;padding:10px 24px;font-weight:600;font-size:14px;text-decoration:none;">Browse Products</a>
  </div>
  <?php else: ?>
 
  <div class="grid md:grid-cols-3 gap-6 items-start">
    <!-- Cart items -->
    <div class="md:col-span-2 flex flex-col gap-4" id="cartItems">
      <?php foreach ($items as $item):
        $emojis = [1=>'📓',2=>'✒️',3=>'🎨',4=>'📎',5=>'📅'];
      ?>
      <div class="cart-row" id="row-<?= $item['cart_id'] ?>"
           style="background:#fff;border:1px solid #d9eeff;border-radius:16px;padding:18px;display:flex;gap:16px;align-items:center;">
        <!-- Product image/emoji -->
        <div style="width:72px;height:72px;background:#EBF5FF;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:30px;flex-shrink:0;">
          <?= isset($emojis[(int)$item['category']]) ? $emojis[(int)$item['category']] : '📦' ?>
        </div>
        <!-- Details -->
        <div style="flex:1;">
          <p style="font-size:12px;color:#94a3b8;"><?= htmlspecialchars($item['category']) ?></p>
          <p style="font-weight:600;color:#0D3E6E;font-size:15px;"><?= htmlspecialchars($item['name']) ?></p>
          <p style="color:#1A6FBA;font-weight:700;font-size:15px;margin-top:3px;">Rs. <?= number_format($item['price']) ?></p>
        </div>
        <!-- Quantity controls -->
        <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
          <button onclick="updateQty(<?= $item['cart_id'] ?>, <?= $item['quantity']-1 ?>)"
                  style="width:30px;height:30px;border-radius:50%;border:1.5px solid #d9eeff;background:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#1A6FBA;">−</button>
          <span id="qty-<?= $item['cart_id'] ?>" style="font-weight:600;min-width:20px;text-align:center;"><?= $item['quantity'] ?></span>
          <button onclick="updateQty(<?= $item['cart_id'] ?>, <?= $item['quantity']+1 ?>)"
                  style="width:30px;height:30px;border-radius:50%;border:1.5px solid #d9eeff;background:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#1A6FBA;">+</button>
        </div>
        <!-- Subtotal -->
        <div style="text-align:right;flex-shrink:0;min-width:80px;">
          <p id="sub-<?= $item['cart_id'] ?>" style="font-weight:700;color:#0D3E6E;">Rs. <?= number_format($item['price'] * $item['quantity']) ?></p>
          <button onclick="removeItem(<?= $item['cart_id'] ?>)"
                  style="color:#ef4444;font-size:12px;background:none;border:none;cursor:pointer;margin-top:4px;font-family:'DM Sans',sans-serif;">Remove</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
 
    <!-- Order summary -->
    <div style="background:#fff;border:1px solid #d9eeff;border-radius:18px;padding:24px;position:sticky;top:80px;">
      <h2 class="serif text-xl mb-4" style="color:#0D3E6E;">Order Summary</h2>
      <div style="display:flex;justify-content:space-between;font-size:14px;color:#64748b;margin-bottom:10px;">
        <span>Subtotal</span>
        <span id="summarySubtotal">Rs. <?= number_format($total) ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:14px;color:#64748b;margin-bottom:10px;">
        <span>Delivery</span>
        <span style="color:#16a34a;"><?= $total >= 500 ? 'FREE' : 'Rs. 80' ?></span>
      </div>
      <div style="height:1px;background:#e2f0ff;margin:14px 0;"></div>
      <div style="display:flex;justify-content:space-between;font-weight:700;color:#0D3E6E;margin-bottom:20px;">
        <span>Total</span>
        <span id="summaryTotal">Rs. <?= number_format($total >= 500 ? $total : $total + 80) ?></span>
      </div>
      <a href="checkout.php"
         style="display:block;text-align:center;background:#3B9EF5;color:#fff;border-radius:50px;padding:13px;font-weight:600;font-size:15px;text-decoration:none;transition:background 0.2s;"
         onmouseover="this.style.background='#1A6FBA'" onmouseout="this.style.background='#3B9EF5'">
        Proceed to Checkout
      </a>
      <a href="products.php" style="display:block;text-align:center;color:#94a3b8;font-size:13px;margin-top:12px;text-decoration:none;">
        ← Continue Shopping
      </a>
    </div>
  </div>
  <?php endif; ?>
</div>
 
<script>
// Product prices for client-side recalculation
const prices = {<?php foreach($items as $i) echo $i['cart_id'].':'.$i['price'].','; ?>};
 
async function updateQty(cart_id, new_qty) {
  const res  = await fetch('../api/update_cart.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ action:'update', cart_id, quantity: new_qty })
  });
  const data = await res.json();
  if (!data.success) return;
 
  if (new_qty <= 0) {
    document.getElementById('row-'+cart_id)?.remove();
  } else {
    document.getElementById('qty-'+cart_id).textContent = new_qty;
    document.getElementById('sub-'+cart_id).textContent = 'Rs. ' + (prices[cart_id] * new_qty).toLocaleString();
  }
  document.querySelector('.cart-count').textContent = data.cart_count;
  updateSummary(data.cart_total);
}
 
async function removeItem(cart_id) {
  await updateQty(cart_id, 0);
}
 
function updateSummary(total_str) {
  const total = parseFloat(total_str.replace(',',''));
  const delivery = total >= 500 ? 0 : 80;
  document.getElementById('summarySubtotal').textContent = 'Rs. ' + total.toLocaleString();
  document.getElementById('summaryTotal').textContent    = 'Rs. ' + (total + delivery).toLocaleString();
}
</script>
<?php include '../includes/footer.php'; ?>