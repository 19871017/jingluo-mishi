<?php
namespace app\services;

class JwtService
{
    private static string $secret = 'escape_room_script_platform_secret_key_2024';
    private static int $expire = 86400 * 7;

    public static function encode(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $headerJson = json_encode($header);
        $headerBase64 = self::base64UrlEncode($headerJson);

        $payload['exp'] = time() + self::$expire;
        $payloadJson = json_encode($payload);
        $payloadBase64 = self::base64UrlEncode($payloadJson);

        $signature = hash_hmac('sha256', $headerBase64 . '.' . $payloadBase64, self::$secret, true);
        $signatureBase64 = self::base64UrlEncode($signature);

        return $headerBase64 . '.' . $payloadBase64 . '.' . $signatureBase64;
    }

    public static function decode(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \Exception('Invalid token format');
        }

        [$headerBase64, $payloadBase64, $signatureBase64] = $parts;

        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', $headerBase64 . '.' . $payloadBase64, self::$secret, true)
        );

        if (!hash_equals($expectedSignature, $signatureBase64)) {
            throw new \Exception('Invalid signature');
        }

        $payload = json_decode(self::base64UrlDecode($payloadBase64), true);

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new \Exception('Token expired');
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $padding = strlen($data) % 4;
        if ($padding) {
            $data .= str_repeat('=', 4 - $padding);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
