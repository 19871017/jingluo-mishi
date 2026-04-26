<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=ggg;charset=utf8mb4', 'ggg', '123456');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare('SELECT username, password FROM admin WHERE username = ? LIMIT 1');
$stmt->execute(['admin']);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

var_dump($admin);
var_dump(password_verify('admin123', $admin['password'] ?? ''));
