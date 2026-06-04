<?php
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}
 
$data     = json_decode(file_get_contents('php://input'), true);
$action   = $data['action']   ?? '';   // 'update' or 'remove'
$cart_id  = (int)($data['cart_id']  ?? 0);
$quantity = (int)($data['quantity'] ?? 1);
$user_id  = (int)$_SESSION['user_id'];
 
if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
    exit();
}
 
// Security: ensure the cart item belongs to the logged-in user
$own = $conn->prepare("SELECT id FROM cart WHERE id = ? AND user_id = ?");
$own->bind_param("ii", $cart_id, $user_id);
$own->execute();
if ($own->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
 
if ($action === 'remove' || $quantity <= 0) {
    $del = $conn->prepare("DELETE FROM cart WHERE id = ?");
    $del->bind_param("i", $cart_id);
    $del->execute();
} elseif ($action === 'update') {
    $upd = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $upd->bind_param("ii", $quantity, $cart_id);
    $upd->execute();
}
 
// Recalculate totals
$res  = $conn->query("SELECT c.id, c.quantity, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");
$cart_total = 0;
$cart_count = 0;
while ($row = $res->fetch_assoc()) {
    $cart_total += $row['quantity'] * $row['price'];
    $cart_count += $row['quantity'];
}
 
echo json_encode([
    'success'     => true,
    'cart_count'  => $cart_count,
    'cart_total'  => number_format($cart_total, 2),
]);
?>
 