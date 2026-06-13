<?php
require_once '../includes/auth_check.php';
checkAuth('user');
require_once '../config/db.php';

$uid = (int)$_SESSION['user_id'];

if (!isset($_GET['data'])) {
    die("Invalid request. Missing data.");
}

$data = $_GET['data'];
$decoded = base64_decode($data);
if (!$decoded) {
    die("Invalid payload.");
}

$payload = json_decode($decoded, true);
if (!$payload) {
    die("Invalid JSON payload.");
}

$transaction_uuid = $conn->real_escape_string($payload['transaction_uuid'] ?? '');
$total_amount = str_replace(',', '', $payload['total_amount'] ?? '');
$transaction_code = $payload['transaction_code'] ?? '';

if (empty($transaction_uuid)) {
    die("Missing transaction UUID.");
}

// Find the order
$res = $conn->query("SELECT * FROM orders WHERE transaction_uuid = '$transaction_uuid' AND user_id = $uid");
if ($res->num_rows === 0) {
    die("Order not found or access denied.");
}
$order = $res->fetch_assoc();
$order_id = $order['id'];

// Proceed to verify with eSewa Transaction Status API
$verify_url = "https://rc-epay.esewa.com.np/api/epay/transaction/status/?product_code=EPAYTEST&total_amount=" . $total_amount . "&transaction_uuid=" . $transaction_uuid;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verify_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response && $http_status === 200) {
    $status_data = json_decode($response, true);
    
    // Status can be COMPLETE, PENDING, CANCELED, etc.
    if (isset($status_data['status']) && $status_data['status'] === 'COMPLETE') {

        $conn->query("UPDATE orders SET payment_status = 'paid' WHERE id = $order_id");
        
        // Redirect to success page
        header("Location: order_confirmation.php?order_id=$order_id");
        exit;
    } else {
        // Verification failed or pending
        $conn->query("UPDATE orders SET payment_status = 'failed' WHERE id = $order_id");
        header("Location: esewa-failure.php");
        exit;
    }
} else {
    // API failed
    die("Error verifying payment with eSewa.");
}
