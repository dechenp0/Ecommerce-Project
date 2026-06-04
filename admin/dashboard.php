<?php
require_once '../includes/auth_check.php';
checkAuth('admin');
require_once '../config/db.php';
$page_title = 'Admin Dashboard – Paperly';
 
// Stats
$total_orders   = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$total_revenue  = $conn->query("SELECT SUM(total) as s FROM orders WHERE status != 'cancelled'")->fetch_assoc()['s'] ?? 0;
$total_products = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$total_users    = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];
 
// Recent orders
$recent_orders = $conn->query("
    SELECT o.id, o.total, o.status, o.created_at, u.full_name
    FROM orders o JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC LIMIT 8
");
 
// Low stock products
$low_stock = $conn->query("SELECT name, stock FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $page_title ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--blue-50:#EBF5FF;--blue-100:#C7E4FF;--blue-200:#93CBFF;--blue-400:#3B9EF5;--blue-600:#1A6FBA;--blue-800:#0D3E6E;}
    body{font-family:'DM Sans',sans-serif;background:#F7FBFF;color:#1a2235;display:flex;min-height:100vh;}
    .serif{font-family:'DM Serif Display',serif;}
    /* Sidebar */
    .sidebar{width:220px;background:#0D3E6E;min-height:100vh;padding:0;flex-shrink:0;position:fixed;top:0;left:0;height:100%;display:flex;flex-direction:column;}
    .sidebar-logo{padding:24px 20px;border-bottom:1px solid rgba(255,255,255,0.1);}
    .sidebar-nav{flex:1;padding:16px 0;}
    .nav-item{display:flex;align-items:center;gap:10px;padding:12px 20px;color:#C7E4FF;font-size:14px;font-weight:500;text-decoration:none;transition:background 0.15s,color 0.15s;cursor:pointer;}
    .nav-item:hover,.nav-item.active{background:rgba(59,158,245,0.2);color:#fff;}
    .nav-item svg{flex-shrink:0;}
    .nav-section{padding:8px 20px;font-size:10px;color:rgba(255,255,255,0.3);font-weight:600;letter-spacing:0.08em;text-transform:uppercase;margin-top:8px;}
    /* Main */
    .main-content{margin-left:220px;flex:1;padding:32px;}
    /* Cards */
    .stat-card{background:#fff;border:1px solid #d9eeff;border-radius:16px;padding:22px 24px;}
    .table-card{background:#fff;border:1px solid #d9eeff;border-radius:18px;overflow:hidden;}
    table{width:100%;border-collapse:collapse;font-size:14px;}
    thead{background:#F7FBFF;}
    th{padding:12px 20px;text-align:left;color:#64748b;font-weight:500;font-size:13px;}
    td{padding:13px 20px;border-top:1px solid #f0f8ff;}
  </style>
</head>
<body>
 
<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <a href="../index.html" style="display:flex;align-items:center;gap:8px;text-decoration:none;">
      <div style="width:30px;height:30px;background:#3B9EF5;border-radius:8px;display:flex;align-items:center;justify-content:center;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 4h10v2H4V8zm0 4h16v2H4v-2zm0 4h10v2H4v-2z" fill="#fff"/></svg>
      </div>
      <span class="serif text-xl text-white">Paperly</span>
    </a>
    <span style="font-size:10px;color:rgba(255,255,255,0.3);margin-top:4px;display:block;">Admin Panel</span>
  </div>
 
  <nav class="sidebar-nav">
    <div class="nav-section">Main</div>
    <a href="dashboard.php" class="nav-item active">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      Dashboard
    </a>
    <div class="nav-section">Manage</div>
    <a href="products.php" class="nav-item">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
      Products
    </a>
    <a href="orders.php" class="nav-item">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      Orders
    </a>
    <a href="users.php" class="nav-item">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      Users
    </a>
    <div class="nav-section">Account</div>
    <a href="../logout.php" class="nav-item" style="color:#f87171;">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout
    </a>
  </nav>
  <div style="padding:16px 20px;font-size:12px;color:rgba(255,255,255,0.25);">
    Logged in as<br/><span style="color:rgba(255,255,255,0.5);"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
  </div>
</aside>
 
<!-- MAIN CONTENT -->
<main class="main-content">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="serif text-3xl" style="color:#0D3E6E;">Dashboard</h1>
      <p style="color:#94a3b8;font-size:14px;margin-top:2px;"><?= date('l, F j, Y') ?></p>
    </div>
  </div>
 
  <!-- Stat cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="stat-card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <span style="font-size:12px;color:#64748b;font-weight:500;">Total Revenue</span>
        <div style="width:36px;height:36px;background:#dcfce7;border-radius:10px;display:flex;align-items:center;justify-content:center;">
          <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
      </div>
      <p class="text-2xl font-bold" style="color:#0D3E6E;">Rs. <?= number_format($total_revenue) ?></p>
    </div>
    <div class="stat-card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <span style="font-size:12px;color:#64748b;font-weight:500;">Total Orders</span>
        <div style="width:36px;height:36px;background:#dbeafe;border-radius:10px;display:flex;align-items:center;justify-content:center;">
          <svg width="16" height="16" fill="none" stroke="#1d4ed8" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
        </div>
      </div>
      <p class="text-2xl font-bold" style="color:#0D3E6E;"><?= $total_orders ?></p>
    </div>
    <div class="stat-card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <span style="font-size:12px;color:#64748b;font-weight:500;">Products</span>
        <div style="width:36px;height:36px;background:#fef9c3;border-radius:10px;display:flex;align-items:center;justify-content:center;">
          <svg width="16" height="16" fill="none" stroke="#854d0e" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
        </div>
      </div>
      <p class="text-2xl font-bold" style="color:#0D3E6E;"><?= $total_products ?></p>
    </div>
    <div class="stat-card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <span style="font-size:12px;color:#64748b;font-weight:500;">Customers</span>
        <div style="width:36px;height:36px;background:#fce7f3;border-radius:10px;display:flex;align-items:center;justify-content:center;">
          <svg width="16" height="16" fill="none" stroke="#9d174d" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
      </div>
      <p class="text-2xl font-bold" style="color:#0D3E6E;"><?= $total_users ?></p>
    </div>
  </div>
 
  <div class="grid md:grid-cols-3 gap-6">
    <!-- Recent orders -->
    <div class="md:col-span-2 table-card">
      <div style="padding:18px 20px;border-bottom:1px solid #e2f0ff;display:flex;align-items:center;justify-content:space-between;">
        <h2 class="serif text-lg" style="color:#0D3E6E;">Recent Orders</h2>
        <a href="orders.php" style="color:#3B9EF5;font-size:13px;font-weight:600;text-decoration:none;">View all →</a>
      </div>
      <table>
        <thead>
          <tr>
            <th>#</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while($ord = $recent_orders->fetch_assoc()):
            $sc = ['pending'=>['#fef9c3','#854d0e'],'processing'=>['#dbeafe','#1e40af'],'shipped'=>['#e0f2fe','#0369a1'],'delivered'=>['#dcfce7','#15803d'],'cancelled'=>['#fee2e2','#dc2626']];
            [$bg,$tc] = $sc[$ord['status']] ?? ['#f1f5f9','#475569'];
          ?>
          <tr>
            <td style="font-weight:600;color:#0D3E6E;">#<?= $ord['id'] ?></td>
            <td><?= htmlspecialchars($ord['full_name']) ?></td>
            <td style="font-weight:600;">Rs. <?= number_format($ord['total']) ?></td>
            <td><span style="background:<?=$bg?>;color:<?=$tc?>;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;text-transform:capitalize;"><?= $ord['status'] ?></span></td>
            <td style="color:#94a3b8;font-size:13px;"><?= date('M d', strtotime($ord['created_at'])) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
 
    <!-- Low stock warning -->
    <div class="table-card">
      <div style="padding:18px 20px;border-bottom:1px solid #e2f0ff;">
        <h2 class="serif text-lg" style="color:#0D3E6E;">⚠ Low Stock</h2>
      </div>
      <div style="padding:16px;">
        <?php while($p = $low_stock->fetch_assoc()): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f0f8ff;">
          <span style="font-size:14px;font-weight:500;color:#1a2235;"><?= htmlspecialchars($p['name']) ?></span>
          <span style="background:<?= $p['stock']==0?'#fee2e2':($p['stock']<5?'#fef9c3':'#e0f2fe') ?>;color:<?= $p['stock']==0?'#dc2626':($p['stock']<5?'#854d0e':'#0369a1') ?>;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;">
            <?= $p['stock'] ?> left
          </span>
        </div>
        <?php endwhile; ?>
        <a href="products.php" style="display:block;text-align:center;margin-top:14px;color:#3B9EF5;font-size:13px;font-weight:600;text-decoration:none;">Manage Products →</a>
      </div>
    </div>
  </div>
</main>
</body>
</html>
 