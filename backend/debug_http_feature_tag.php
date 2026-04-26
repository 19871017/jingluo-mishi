<?php

$loginPayload = json_encode([
    'username' => 'admin',
    'password' => 'admin123',
], JSON_UNESCAPED_UNICODE);

$loginContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json; charset=utf-8\r\n",
        'content' => $loginPayload,
        'ignore_errors' => true,
    ],
]);

$loginResponse = file_get_contents('http://127.0.0.1:8090/api/admin/login', false, $loginContext);
echo $loginResponse . PHP_EOL;

$loginData = json_decode($loginResponse, true);
$token = $loginData['data']['token'] ?? '';

$payload = json_encode([
    'name' => '接口中文测试',
    'sort_order' => 996,
], JSON_UNESCAPED_UNICODE);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json; charset=utf-8\r\nAuthorization: Bearer {$token}\r\nAdmin-Token: {$token}\r\n",
        'content' => $payload,
        'ignore_errors' => true,
    ],
]);

$response = file_get_contents('http://127.0.0.1:8090/api/admin/feature-tags', false, $context);
echo $response . PHP_EOL;
