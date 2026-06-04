<?php
session_start();
require_once '../includes/auth_check.php';
checkAuth('admin');
 
header('Content-Type: application/json');
 
if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}
 
$file     = $_FILES['image'];
$allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$max_size = 5 * 1024 * 1024; // 5MB
 
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error']);
    exit();
}
 
if (!in_array($file['type'], $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, WEBP, GIF allowed']);
    exit();
}
 
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
    exit();
}
 
// Create upload directory if not exists
$upload_dir = '../assets/images/products/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
 
// Generate unique filename
$ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('product_', true) . '.' . strtolower($ext);
$dest     = $upload_dir . $filename;
 
if (move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode([
        'success'   => true,
        'filename'  => $filename,
        'image_url' => '../assets/images/products/' . $filename
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
}
?>