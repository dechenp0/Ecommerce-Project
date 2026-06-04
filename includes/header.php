<?php
if (session_status() === PHP_SESSION_NONE) session_start();
 
// Get cart count from DB
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../config/db.php';
    $uid  = $_SESSION['user_id'];
    $cres = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $uid");
    $crow = $cres->fetch_assoc();
    $cart_count = $crow['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $page_title ?? 'Paperly' ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{ --blue-50:#EBF5FF;--blue-100:#C7E4FF;--blue-200:#93CBFF;--blue-400:#3B9EF5;--blue-600:#1A6FBA;--blue-800:#0D3E6E; }
    body { font-family:'DM Sans',sans-serif; background:#F7FBFF; color:#1a2235; }
    .serif { font-family:'DM Serif Display',serif; }
    .nav-link { transition:color 0.2s; }
    .nav-link:hover, .nav-link.active { color:var(--blue-600); }
    .cart-count { position:absolute;top:-6px;right:-8px;background:var(--blue-400);color:#fff;width:18px;height:18px;border-radius:50%;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center; }
    <?= $extra_css ?? '' ?>
  </style>
</head>
<body>
<nav class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-blue-100 shadow-sm">
  <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="../index.html" class="flex items-center gap-2">
      <div style="width:32px;height:32px;background:var(--blue-400);border-radius:9px;display:flex;align-items:center;justify-content:center;">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 4h10v2H4V8zm0 4h16v2H4v-2zm0 4h10v2H4v-2z" fill="#fff"/></svg>
      </div>
      <span class="serif text-2xl" style="color:#0D3E6E;">Paperly</span>
    </a>
    <div class="hidden md:flex items-center gap-7 text-sm font-medium text-gray-600">
      <a href="products.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='products.php')?'active':'' ?>">Products</a>
      <a href="orders.php"   class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='orders.php')?'active':'' ?>">My Orders</a>
    </div>
    <div class="flex items-center gap-3">
      <a href="cart.php" class="relative p-2 rounded-full hover:bg-blue-50 transition-colors">
        <svg width="22" height="22" fill="none" stroke="#1A6FBA" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <span class="cart-count"><?= $cart_count ?></span>
      </a>
      <div class="relative group">
        <button class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-blue-600">
          <div style="width:32px;height:32px;background:var(--blue-100);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--blue-600);font-size:13px;">
            <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
          </div>
          <span class="hidden md:block"><?= htmlspecialchars($_SESSION['full_name'] ?? '') ?></span>
        </button>
        <div class="absolute right-0 top-10 bg-white rounded-xl shadow-lg border border-blue-50 py-2 w-40 hidden group-hover:block z-50">
          <a href="dashboard.php" class="block px-4 py-2 text-sm text-gray-600 hover:bg-blue-50">Dashboard</a>
          <a href="orders.php"    class="block px-4 py-2 text-sm text-gray-600 hover:bg-blue-50">My Orders</a>
          <hr class="my-1 border-blue-50"/>
          <a href="../logout.php" class="block px-4 py-2 text-sm text-red-500 hover:bg-red-50">Logout</a>
        </div>
      </div>
    </div>
  </div>
</nav>