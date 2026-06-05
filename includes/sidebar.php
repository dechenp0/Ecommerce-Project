<?php
// includes/user_sidebar.php
// Shared sidebar for all user pages
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
  :root {
    --blue-50:#EBF5FF;--blue-100:#C7E4FF;--blue-200:#93CBFF;
    --blue-400:#3B9EF5;--blue-600:#1A6FBA;--blue-800:#0D3E6E;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'DM Sans', sans-serif;
    background: #F0F7FF;
    color: #1a2235;
    display: flex;
    min-height: 100vh;
  }
  .serif { font-family: 'DM Serif Display', serif; }

  /* ── Sidebar ── */
  .u-sidebar {
    width: 240px;
    background: linear-gradient(160deg, #0D3E6E 0%, #0a2e54 100%);
    min-height: 100vh;
    position: fixed;
    top: 0; left: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
    z-index: 100;
    box-shadow: 4px 0 24px rgba(13,62,110,0.12);
  }
  .u-sidebar-logo {
    padding: 28px 22px 22px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
  }
  .u-sidebar-nav { flex: 1; padding: 18px 0; overflow-y: auto; }
  .u-nav-section {
    padding: 6px 22px 4px;
    font-size: 10px;
    color: rgba(255,255,255,0.28);
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-top: 10px;
  }
  .u-nav-item {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 11px 22px;
    color: #A8D4F8;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.15s, color 0.15s, padding-left 0.15s;
    position: relative;
    border-radius: 0;
  }
  .u-nav-item:hover {
    background: rgba(59,158,245,0.15);
    color: #fff;
    padding-left: 26px;
  }
  .u-nav-item.active {
    background: rgba(59,158,245,0.22);
    color: #fff;
  }
  .u-nav-item.active::before {
    content: '';
    position: absolute;
    left: 0; top: 6px; bottom: 6px;
    width: 3px;
    background: #3B9EF5;
    border-radius: 0 3px 3px 0;
  }
  .u-nav-item svg { flex-shrink: 0; opacity: 0.85; }
  .u-nav-item.active svg, .u-nav-item:hover svg { opacity: 1; }

  /* Cart badge */
  .cart-badge {
    margin-left: auto;
    background: #3B9EF5;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 1px 7px;
    border-radius: 20px;
    min-width: 20px;
    text-align: center;
  }

  /* ── Main ── */
  .u-main { margin-left: 240px; flex: 1; padding: 36px 40px; min-width: 0; }

  @media (max-width: 768px) {
    .u-sidebar { width: 200px; }
    .u-main { margin-left: 200px; padding: 24px 20px; }
  }
</style>

<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet"/>

<aside class="u-sidebar">
  <div class="u-sidebar-logo">
    <a href="../index.html" style="display:flex;align-items:center;gap:9px;text-decoration:none;">
      <div style="width:32px;height:32px;background:#3B9EF5;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 4h10v2H4V8zm0 4h16v2H4v-2zm0 4h10v2H4v-2z" fill="#fff"/></svg>
      </div>
      <span class="serif" style="font-size:20px;color:#fff;letter-spacing:0.01em;">Paperly</span>
    </a>
    <span style="font-size:11px;color:rgba(255,255,255,0.3);margin-top:5px;display:block;">My Account</span>
  </div>

  <nav class="u-sidebar-nav">
    <div class="u-nav-section">Overview</div>
    <a href="dashboard.php" class="u-nav-item <?= $current_page==='dashboard.php'?'active':'' ?>">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      Dashboard
    </a>

    <div class="u-nav-section">Shopping</div>
    <a href="products.php" class="u-nav-item <?= $current_page==='products.php'?'active':'' ?>">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
      Browse Products
    </a>
    <a href="cart.php" class="u-nav-item <?= $current_page==='cart.php'?'active':'' ?>">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
      My Cart
      <?php
        $uid_sb = (int)$_SESSION['user_id'];
        $cart_cnt = $conn->query("SELECT SUM(quantity) as c FROM cart WHERE user_id=$uid_sb")->fetch_assoc()['c'] ?? 0;
        if ($cart_cnt > 0): ?>
        <span class="cart-badge"><?= $cart_cnt ?></span>
      <?php endif; ?>
    </a>
    <a href="orders.php" class="u-nav-item <?= $current_page==='orders.php'?'active':'' ?>">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      My Orders
    </a>

    <a href="../logout.php" class="u-nav-item" style="color:#f87171;margin-top:4px;">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout
    </a>
  </nav>

  <!-- User info at bottom -->
  <div style="padding:16px 22px;border-top:1px solid rgba(255,255,255,0.07);">
    <div style="display:flex;align-items:center;gap:10px;">
      <div style="width:34px;height:34px;background:linear-gradient(135deg,#3B9EF5,#1A6FBA);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:#fff;flex-shrink:0;">
        <?= strtoupper(substr($_SESSION['full_name'],0,1)) ?>
      </div>
      <div style="overflow:hidden;">
        <p style="font-size:13px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($_SESSION['full_name']) ?></p>
        <p style="font-size:11px;color:rgba(255,255,255,0.35);">Customer</p>
      </div>
    </div>
  </div>
</aside>