<?php
header('Content-Type: application/json');
require_once '../config/db.php';
 
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search      = isset($_GET['search'])   ? trim($_GET['search'])   : '';
 
$sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image,
               c.name AS category_name, c.id AS category_id
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1";
 
$params = [];
$types  = '';
 
if ($category_id > 0) {
    $sql    .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types   .= 'i';
}
 
if (!empty($search)) {
    $sql    .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $like     = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}
 
$sql .= " ORDER BY p.id DESC";
 
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result   = $stmt->get_result();
$products = [];
 
while ($row = $result->fetch_assoc()) {
    $row['image_url'] = !empty($row['image'])
        ? '../assets/images/products/' . $row['image']
        : null;
    $products[] = $row;
}
 
echo json_encode(['success' => true, 'products' => $products]);
?>