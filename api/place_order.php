<?php
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$uid  = (int)$_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['full_name','phone','address','city','payment_method'];
foreach ($required as $field) {
    if (empty(trim($data[$field] ?? ''))) {
        echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
        exit;
    }
}

$full_name      = $conn->real_escape_string(trim($data['full_name']));
$phone          = $conn->real_escape_string(trim($data['phone']));
$address        = $conn->real_escape_string(trim($data['address']));
$city           = $conn->real_escape_string(trim($data['city']));
$district       = $conn->real_escape_string(trim($data['district'] ?? ''));
$note           = $conn->real_escape_string(trim($data['note'] ?? ''));
$payment_method = $conn->real_escape_string(trim($data['payment_method']));

// Fetch cart
$cart_res = $conn->query("
    SELECT c.quantity, p.id as product_id, p.price, p.stock, p.name
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = $uid
");

$cart_items = [];
$subtotal   = 0;
while ($row = $cart_res->fetch_assoc()) {
    $cart_items[] = $row;
    $subtotal    += $row['price'] * $row['quantity'];
}

if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty']);
    exit;
}

// Check stock
foreach ($cart_items as $item) {
    if ($item['quantity'] > $item['stock']) {
        echo json_encode([
            'success' => false,
            'message' => "Sorry, \"{$item['name']}\" only has {$item['stock']} in stock."
        ]);
        exit;
    }
}

$delivery_fee = $subtotal >= 500 ? 0 : 80;
$grand_total  = $subtotal + $delivery_fee;

// Build delivery address JSON
$delivery_address = $conn->real_escape_string(json_encode([
    'full_name' => $full_name,
    'phone'     => $phone,
    'address'   => $address,
    'city'      => $city,
    'district'  => $district,
    'note'      => $note
]));

$payment_status = 'pending';

// Begin transaction
$conn->begin_transaction();

try {
    // 1. Insert order — created_at has DEFAULT current_timestamp() so skip it
    $conn->query("
        INSERT INTO orders (user_id, total, delivery_fee, payment_method, payment_status, delivery_address, status)
        VALUES ($uid, $grand_total, $delivery_fee, '$payment_method', '$payment_status', '$delivery_address', 'pending')
    ");

    if ($conn->error) throw new Exception($conn->error);

    $order_id = $conn->insert_id;

    // 2. Insert order items + reduce stock
    foreach ($cart_items as $item) {
        $pid   = (int)$item['product_id'];
        $qty   = (int)$item['quantity'];
        $price = (float)$item['price'];

        $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $pid, $qty, $price)");
        if ($conn->error) throw new Exception($conn->error);

        $conn->query("UPDATE products SET stock = stock - $qty WHERE id = $pid");
        if ($conn->error) throw new Exception($conn->error);
    }

    // 3. Clear cart
    $conn->query("DELETE FROM cart WHERE user_id = $uid");

    $conn->commit();

    echo json_encode([
        'success'  => true,
        'order_id' => $order_id,
        'message'  => 'Order placed successfully'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}