<?php
require_once '../config/db.php';

$full_name = 'Admin';
$email     = 'admin@gmail.com';
$password  = 'admin123';
$hashed    = password_hash($password, PASSWORD_DEFAULT);
$role      = 'admin';

$stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $full_name, $email, $hashed, $role);

if ($stmt->execute()) {
    echo "Admin created successfully!";
} else {
    echo "Error: " . $conn->error;
}
