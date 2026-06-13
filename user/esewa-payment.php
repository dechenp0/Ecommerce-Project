<?php
require_once '../includes/auth_check.php';
checkAuth('user');
require_once '../config/db.php';

$uid = (int)$_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

$res = $conn->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $uid AND payment_status = 'pending'");
if ($res->num_rows === 0) {
    die("Invalid order or already paid.");
}

$order = $res->fetch_assoc();

$amount = $order['total'];
$tax_amount = 0;
$product_service_charge = 0;
$product_delivery_charge = 0;
$total_amount = $order['total'];

$transaction_uuid = $order['transaction_uuid'];
$product_code = 'EPAYTEST';
$secret = '8gBm/:&EnhH.1/q';

// The message format must strictly match: total_amount,transaction_uuid,product_code
$message = "total_amount=$total_amount,transaction_uuid=$transaction_uuid,product_code=$product_code";
$signature = base64_encode(hash_hmac('sha256', $message, $secret, true));

$success_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/esewa-success.php";
$failure_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/esewa-failure.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to eSewa...</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f5f9ff; color: #0D3E6E; }
        .loader { border: 4px solid #d9eeff; border-top: 4px solid #3B9EF5; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 16px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .container { text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="loader"></div>
        <h2>Redirecting to eSewa</h2>
        <p>Please wait while we redirect you to the payment gateway...</p>
        
        <form id="esewaForm" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST" style="display: none;">
            <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">
            <input type="hidden" name="tax_amount" value="<?= htmlspecialchars($tax_amount) ?>">
            <input type="hidden" name="total_amount" value="<?= htmlspecialchars($total_amount) ?>">
            <input type="hidden" name="transaction_uuid" value="<?= htmlspecialchars($transaction_uuid) ?>">
            <input type="hidden" name="product_code" value="<?= htmlspecialchars($product_code) ?>">
            <input type="hidden" name="product_service_charge" value="<?= htmlspecialchars($product_service_charge) ?>">
            <input type="hidden" name="product_delivery_charge" value="<?= htmlspecialchars($product_delivery_charge) ?>">
            <input type="hidden" name="success_url" value="<?= htmlspecialchars($success_url) ?>">
            <input type="hidden" name="failure_url" value="<?= htmlspecialchars($failure_url) ?>">
            <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
            <input type="hidden" name="signature" value="<?= htmlspecialchars($signature) ?>">
        </form>
    </div>
    
    <script>
        window.onload = function() {
            document.getElementById('esewaForm').submit();
        };
    </script>
</body>
</html>
