<?php
require_once '../includes/auth_check.php';
checkAuth('user');
$page_title = 'Payment Failed';
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
  <div style="background:#fff;border:1px solid #fee2e2;border-radius:20px;padding:56px 40px;text-align:center;max-width:600px;margin:40px auto;box-shadow:0 12px 32px rgba(220,38,38,0.05);">
    <div style="width:80px;height:80px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
      <svg width="40" height="40" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </div>
    <h1 class="serif" style="font-size:32px;color:#0D3E6E;margin-bottom:12px;">Payment Failed</h1>
    <p style="color:#64748b;font-size:15px;margin-bottom:32px;line-height:1.6;">
      We couldn't process your payment. Your order has been saved as pending in our system. You can view it in your orders page or try placing a new order.
    </p>
    <div style="display:flex;align-items:center;justify-content:center;gap:12px;">
      <a href="dashboard.php" style="background:#0D3E6E;color:#fff;border-radius:50px;padding:12px 28px;font-weight:600;font-size:14px;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='#3B9EF5'" onmouseout="this.style.background='#0D3E6E'">
        Go to Dashboard
      </a>
      <a href="orders.php" style="border:1.5px solid #d9eeff;color:#64748b;border-radius:50px;padding:12px 28px;font-weight:600;font-size:14px;text-decoration:none;background:#fff;transition:border-color 0.2s;" onmouseover="this.style.borderColor='#3B9EF5';this.style.color='#3B9EF5'" onmouseout="this.style.borderColor='#d9eeff';this.style.color='#64748b'">
        View My Orders
      </a>
    </div>
  </div>
</main>

</body>
</html>
