<?php
require_once '../includes/auth_check.php';
checkAuth('admin');
require_once '../config/db.php';
$page_title = 'Manage Users - Paperly';

$users = $conn->query("SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $page_title ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--blue-400:#3B9EF5;--blue-600:#1A6FBA;--blue-800:#0D3E6E;}
    body{font-family:'DM Sans',sans-serif;background:#F7FBFF;color:#1a2235;display:flex;min-height:100vh;}
    .serif{font-family:'DM Serif Display',serif;}
    .sidebar{width:220px;background:#0D3E6E;min-height:100vh;position:fixed;top:0;left:0;height:100%;}
    .sidebar-logo{padding:24px 20px;border-bottom:1px solid rgba(255,255,255,0.1);}
    .nav-item{display:flex;align-items:center;gap:10px;padding:12px 20px;color:#C7E4FF;font-size:14px;font-weight:500;text-decoration:none;transition:background 0.15s;}
    .nav-item:hover,.nav-item.active{background:rgba(59,158,245,0.2);color:#fff;}
    .nav-section{padding:8px 20px;font-size:10px;color:rgba(255,255,255,0.3);font-weight:600;letter-spacing:0.08em;text-transform:uppercase;margin-top:8px;}
    .main-content{margin-left:220px;flex:1;padding:32px;}
    table{width:100%;border-collapse:collapse;font-size:14px;}
    thead{background:#F7FBFF;}
    th{padding:12px 16px;text-align:left;color:#64748b;font-weight:500;font-size:13px;}
    td{padding:13px 16px;border-top:1px solid #f0f8ff;}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-logo">
    <a href="../index.html" style="display:flex;align-items:center;gap:8px;text-decoration:none;">
      <div style="width:30px;height:30px;background:#3B9EF5;border-radius:8px;display:flex;align-items:center;justify-content:center;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 4h10v2H4V8zm0 4h16v2H4v-2zm0 4h10v2H4v-2z" fill="#fff"/></svg>
      </div>
      <span class="serif text-xl text-white">Paperly</span>
    </a>
  </div>
  <nav style="padding:16px 0;">
    <a href="dashboard.php" class="nav-item"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg> Dashboard</a>
    <a href="products.php" class="nav-item"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg> Products</a>
    <a href="orders.php" class="nav-item"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg> Orders</a>
    <a href="users.php" class="nav-item active"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg> Users</a>
    <a href="../logout.php" class="nav-item" style="color:#f87171;margin-top:8px;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Logout</a>
  </nav>
</aside>
 
<main class="main-content">
  <h1 class="serif text-3xl mb-2" style="color:#0D3E6E;">Users</h1>
  <p style="color:#94a3b8;font-size:14px;margin-bottom:24px;">Manage all registered users</p>
 
  <div style="background:#fff;border:1px solid #d9eeff;border-radius:18px;overflow:hidden;">
    <table>
      <thead>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr>
      </thead>
      <tbody>
        <?php while($u = $users->fetch_assoc()): ?>
        <tr>
          <td style="font-weight:700;color:#0D3E6E;">#<?= $u['id'] ?></td>
          <td style="font-weight:500;"><?= htmlspecialchars($u['full_name']) ?></td>
          <td style="color:#64748b;font-size:13px;"><?= htmlspecialchars($u['email']) ?></td>
          <td>
            <span style="background:<?= $u['role'] === 'admin' ? '#fef9c3' : '#e0f2fe' ?>;color:<?= $u['role'] === 'admin' ? '#854d0e' : '#0369a1' ?>;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;text-transform:uppercase;">
              <?= htmlspecialchars($u['role']) ?>
            </span>
          </td>
          <td style="color:#94a3b8;font-size:13px;"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
