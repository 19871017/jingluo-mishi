<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=ggg;charset=utf8mb4', 'ggg', '123456');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo $pdo->query('SELECT @@character_set_connection')->fetchColumn() . PHP_EOL;
echo $pdo->query('SELECT @@character_set_database')->fetchColumn() . PHP_EOL;

$name = '联调中文标签';
$stmt = $pdo->prepare('INSERT INTO feature_tag(name, sort_order) VALUES(?, ?)');
$stmt->execute([$name, 999]);

echo $pdo->lastInsertId() . PHP_EOL;
