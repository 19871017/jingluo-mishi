<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=ggg;charset=utf8mb4', 'ggg', '123456');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$hash = password_hash('admin123', PASSWORD_BCRYPT);
$stmt = $pdo->prepare('UPDATE admin SET password = ? WHERE username = ?');
$stmt->execute([$hash, 'admin']);

echo $hash . PHP_EOL;
