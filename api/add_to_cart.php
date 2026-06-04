<?php
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in', 'redirect' => '../login.php']);
    exit();
}
 
$data       = json_decode(file_get_contents('php://input'), true);
$product_id = (int)($data['product_id'] ?? 0);
$quantity   = (int)($data['quantity']   ?? 1);
$user_id    = (int)$_SESSION['user_id'];
 
if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    exit();
}
 
// Check product stock
$pstmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
$pstmt->bind_param("i", $product_id);
$pstmt->execute();
$pres = $pstmt->get_result()->fetch_assoc();
 
if (!$pres || $pres['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
    exit();
}
 
// Check if already in cart
$check = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$existing = $check->get_result()->fetch_assoc();
 
if ($existing) {
    $new_qty  = $existing['quantity'] + $quantity;
    $upd      = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $upd->bind_param("ii", $new_qty, $existing['id']);
    $upd->execute();
} else {
    $ins = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $ins->bind_param("iii", $user_id, $product_id, $quantity);
    $ins->execute();
}
 
// Return updated cart count
$cres  = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id");
$count = $cres->fetch_assoc()['total'] ?? 0;
 
echo json_encode(['success' => true, 'message' => 'Added to cart', 'cart_count' => (int)$count]);
?>